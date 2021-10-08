<?php

namespace App\Service;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\UploadFileJob;
use Exception;

class UploadService 
{

    public function __construct()
    {
    }

    public function uploadFile($fileObject, &$msg)
    {

        if ($fileObject->getError() == UPLOAD_ERR_FORM_SIZE) {
            $msg = '上传文件大小超出限制';
            return false;
        }
        //check 
        $this->check($fileObject);

        //path
        $file = $fileObject->getRealPath();
        $fileName = $fileObject->getClientOriginalName();
        $uploadTime = time();

        $filePath = $this->putFilePath($fileName);
        app('filesystem')->put($filePath, file_get_contents($file));

        $data = [
            'form_name' => $fileName,
            'form_size' => $fileObject->getSize(),
            'form_path' => $filePath,
            'status' => '2', //等待处理
        ];

        //存库
        $data['id'] = DB::table('form_record')->insertGetId($data);
        //队列
        $gotoJob = (new UploadFileJob($data))->onQueue('slow');
        app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($gotoJob);

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
        $uploade_file_info = DB::select('select * from form_record where id = :id', ['id' => $data['id']]);
        if (empty($uploade_file_info)) {
            return true;
        }

        if ($uploade_file_info['status'] != '2') {
            return true;
        }

        DB::update('update form_record set status = 3 where id = ?', [$data['id']]);

        $filePath = $this->putFilePath($data['form_name']);
        $fileUrl = storage_path('app') . '/' . app('filesystem')->url($filePath);

        
        
    }

    private function putFilePath($fileName)
    {
        return'import/' . $fileName;
    }

    private function check($fileObject)
    {
        $extension = $fileObject->getClientOriginalExtension();
        // if($extension != 'xlsx') {
        //     throw new Exception('只支持Excel文件格式');
        // }
    }
}
