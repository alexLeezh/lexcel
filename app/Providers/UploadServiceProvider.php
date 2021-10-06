<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;

class UploadServiceProvider extends ServiceProvider
{
    
    /**
     * 为null表示还没有被实例化
     * AbstractTemplate为抽象类
     * Object为原下载模板的老代码，还未迭代
     * @var null|AbstractTemplate|Object
     */
    public $uploadFile = null;

    private $entityRepository;

    public function __construct()
    {
        $this->entityRepository = app('registry')->getManager('default')->getRepository(UploadeFile::class);
    }

    /**
     * 获取上传文件的实例化类
     */
    public function getUpdateFile($fileType)
    {
        if (!$this->uploadFile) {
            $uploadFileClass = config('filesystems.upload_file_handle.' . $fileType);
            $this->uploadFile = new $uploadFileClass;
        }

        return $this->uploadFile;
    }

    private function putFilePath($companyId, $fileType, $uploadTime, $fileName)
    {
        return $fileType . '/' . $companyId . '/' . $uploadTime . '/' . md5($fileName);
    }

    /**
     * 上传文件，并且保存文件上传记录
     *
     * @param object $fileObject SplFileInfo
     */
    public function uploadFile($companyId, $distributorId, $fileType, $fileObject, $shouldQueue = true)
    {
        $this->getUpdateFile($fileType);

        if ($fileObject->getError() == UPLOAD_ERR_FORM_SIZE) {
            throw new BadRequestHttpException('上传文件大小超出限制');
        }

        $this->uploadFile->check($fileObject);

        $uploadTime = time();
        $fileName = $fileObject->getClientOriginalName();
        $filePath = $this->putFilePath($companyId, $fileType, $uploadTime, $fileName);

        $file = $fileObject->getRealPath();

        //如果是社区活动商品，实时处理返回结果
        if (method_exists($this->uploadFile, 'syncProcess')) {
            $data = $this->uploadFile->syncProcess($file);
            return $data;
        }

        if (method_exists($this->uploadFile, 'getFileSystem')) {
            $this->uploadFile->getFileSystem()->put($filePath, file_get_contents($file));
        } else {
            app('filesystem')->put($filePath, file_get_contents($file));
        }

        $data = [
            'company_id' => $companyId,
            'file_name' => $fileName,
            'file_size' => $fileObject->getClientSize(),
            'handle_status' => 'wait', //等待处理
            'file_type' => $fileType,
            'handle_line_num' => 0,
            'created' => $uploadTime,
            'distributor_id' => $distributorId,
            'left_job_num' => 1, //默认剩余一个待处理的子任务
        ];
        $data = $this->entityRepository->create($data);

        if ($shouldQueue) {
            // 将处理文件加入到队列
            $gotoJob = (new UploadFileJob($data))->onQueue('slow');
            app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($gotoJob);
        } else {
            $this->handleUploadFile($data, $shouldQueue);
        }

        return $data;
    }

    /**
     * 处理上传文件
     * @param $data
     * @param bool $shouldQueue
     * @return bool
     */
    public function handleUploadFile($data, bool $shouldQueue = true)
    {
        //只处理 wait 状态下的任务，防止有导入失败的文件通过队列反复执行
        $uploade_file_info = $this->entityRepository->getInfoById($data['id']);
        if (empty($uploade_file_info)) {
            return true;
        }

        if ($uploade_file_info['handle_status'] != 'wait') {
            return true;
        }

        $companyId = $data['company_id'] ?? 0;

        $this->entityRepository->updateOneBy(['id' => $data['id']], ['handle_status' => 'processing']);

        $uploadFile = $this->getUpdateFile($data['file_type']);

        $filePath = $this->putFilePath($data['company_id'], $data['file_type'], $data['created'], $data['file_name']);
        if (method_exists($uploadFile, 'getFilePath')) {
            $fileUrl = $uploadFile->getFilePath($filePath);
        } else {
            $fileUrl = storage_path('app') . '/' . app('filesystem')->url($filePath);
        }

        $errorData = [];
        $successLine = 0;
        $errorLine = 0;

        //设置头部
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $column = [];
        $headerData = [];
        try {
            $results = [];
            app('excel')->load($fileUrl, function ($reader) use (&$results) //reader读取excel内容
            {
                $reader = $reader->getSheet(0);//excel第一张sheet
                $results = $reader->toArray();
            }, null, true);

            $headerData = array_filter($results[0]);
            array_walk($headerData, function (&$value) {
                $value = preg_replace("/\s|　/", "", $value);
            });
            $column = $this->headerHandle($headerData, $companyId);
            $headerSuccess = true;
            unset($results[0]);
        } catch (\Exception $e) {
            $headerSuccess = false;
            $errorLine++;
            $headerTitle = $this->uploadFile->getHeaderTitle($companyId);
            $columnNum = count($headerTitle['all']);
            $errorData[] = array_merge(array_fill(0, $columnNum, ''), ['头部标题或Excel解析错误', $e->getMessage()]);
            //$this->errorHandle($data['id'], $errorData);
        } catch (\Throwable $e) {
            $headerSuccess = false;
            $errorLine++;
            $headerTitle = $this->uploadFile->getHeaderTitle($companyId);
            $columnNum = count($headerTitle['all']);
            $errorData[] = array_merge(array_fill(0, $columnNum, ''), ['头部标题或Excel解析错误', $e->getMessage()]);
            //$this->errorHandle($data['id'], $errorData);
        }

        // 如果头部是正确的，才会处理到下一步
        if ($headerSuccess) {
            $newAarray = array_chunk($results, 500, true);
            $this->entityRepository->updateOneBy(['id' => $data['id']], ['left_job_num' => count($newAarray)]);
            foreach ($newAarray as $k => $nresults) {
                $gotoJob = new ImportDataJob($data, $nresults, $column, count($newAarray) - $k, $headerData);
                if ($shouldQueue) {
                    app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($gotoJob->onQueue('slow'));
                } else {
                    $gotoJob->handle();
                }
            }
        } else {
            $this->finishHandle($data['id'], $successLine, $errorLine, $filePath, $errorData, 1, $headerData);
        }

        if (method_exists($this->uploadFile, 'finishHandle')) {
            $this->uploadFile->finishHandle();
        }
    }
}
