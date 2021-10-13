<?php

namespace App\Service;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\UploadFileJob;
use App\Jobs\ExampleJob;
use App\Jobs\UploadFileDataJob;
use App\Imports\SchoolImport;
use Illuminate\Support\Facades\Redis;
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

        $filePath = $this->putFilePath($uploadTime, $fileName);
        app('filesystem')->put($filePath, file_get_contents($file));

        $data = [
            'form_name' => $fileName,
            'form_size' => $fileObject->getSize(),
            'form_path' => $filePath,
            'created' => $uploadTime,
            'status' => 2, //等待处理
        ];

        //存库
        $data['id'] = DB::table('form_record')->insertGetId($data);
        //队列
        dispatch(new UploadFileJob($data));
        // $gotoJob = (new UploadFileJob($data))->onQueue('slow');
        // app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($gotoJob);

        return $data;
    }

    /**
     * 处理上传文件
     * @param $data
     * @return bool
     */
    public function handleUploadFile($data)
    {
        $uploade_file_info = DB::table('form_record')->where('id', $data['id'])->first();
        if (empty($uploade_file_info)) {
            return true;
        }
        if ($uploade_file_info->status != '2') {
            return true;
        }

        DB::update('update form_record set status = 3 where id = ?', [$data['id']]);

        $filePath = $this->putFilePath($data['created'], $data['form_name']);
        $fileUrl = storage_path('app') . '/' .$filePath;
        //导入
        $import = new SchoolImport();
        $import->onlySheets('基础基111','基础基211','基础基411','基础基4211');
        Excel::import($import, $fileUrl);

        //执行数据清理
        dispatch(new UploadFileDataJob($data));
        
    }

    /**
     * 处理上传后数据整理
     * @param   $data 
     * @return 
     */
    public function handleUploadData($data)
    {
        $batch = app('session')->get('report_hash');
        !$batch && $batch = Redis::get('report_hash');
        Log::info(  date( 'Ymdhis' , time() )  . $batch);
        if (!$batch) {
            throw new Exception('请联系管理员！');
        }
        $preData = app('db')->select("SELECT * FROM pre_sheet_data where report_hash = '".$batch."'  ");
        
        $foundArr = [];
        foreach ($preData as $key => $value) {
            !$value->found_divisor && $value->found_divisor = 0;
            !$value->found_divider && $value->found_divider = 0;
            $foundArr[$value->found_ind][] = $value;
        }
        //有需处理的数据
        $insert_row = [];
        $global_config = config('ixport.SCHOOL_IMPORT_FOUND_INDEX');
        $is_standard = 0;
        if ($foundArr) {
            foreach ($foundArr as $key => $value) {
                $foundval = [];
                $ind_config = [];
                if (count($value) == 2) {
                    $foundval = $this->Object2Array(array_values($value));
                    $ind_config = $global_config[$key];
                    $insert_row['school'] = $foundval[0]['school'];
                    $insert_row['school_type'] = $foundval[0]['school_type'];
                    $insert_row['basic_val'] = $ind_config['basic_val'];
                    $insert_row['standard_val'] = $ind_config['standard_val'];
                    $insert_row['found_name'] = $ind_config['found_name'];
                    $insert_row['found_ind'] = $foundval[0]['found_ind'];
                    $insert_row['report_type'] = $foundval[0]['report_type'];
                    $insert_row['form_id'] = $data['id'];

                    if (isset($foundval[0]['found_divisor']) && $foundval[0]['found_divisor'] > 0) {
                        $insert_row['found_val'] = $this->valFormat($ind_config['ratio'],$ind_config['unit'],$foundval[0]['found_divisor'],$foundval[1]['found_divider'],$ind_config['standard_val'],$is_standard);
                    }else{
                        $insert_row['found_val'] = $this->valFormat($ind_config['ratio'],$ind_config['unit'],$foundval[1]['found_divisor'],$foundval[0]['found_divider'],$ind_config['standard_val'],$is_standard);
                    }
                    $insert_row['is_standard'] = $is_standard;
                }

                if (count($value) > 2) {
                    $foundval = array_values($value);
                    $ind_config = $global_config[$foundval[0]['found_ind']];

                    $insert_row['school'] = $foundval[0]['school'];
                    $insert_row['school_type'] = $foundval[0]['school_type'];
                    $insert_row['basic_val'] = $ind_config['basic_val'];
                    $insert_row['standard_val'] = $ind_config['standard_val'];
                    $insert_row['found_name'] = $ind_config['found_name'];
                    $insert_row['found_ind'] = $foundval[0]['found_ind'];
                    $insert_row['report_type'] = $foundval[0]['report_type'];
                    $insert_row['form_id'] = $data['id'];

                    $found_divisor = 0;
                    $found_divider = 0;
                    foreach ($foundval as $k => $v) {
                        $found_divisor +=$v['found_divisor'];
                        $found_divider +=$v['found_divider'];
                    }
                    $insert_row['found_val'] = $this->valFormat($ind_config['ratio'],$ind_config['unit'],$found_divisor,$found_divider,$ind_config['standard_val'],$is_standard);
                    $insert_row['is_standard'] = $is_standard;
                }
                //更新导出表
                DB::table('sheet_record')->insert($insert_row);
            }
            DB::table('pre_sheet_data')->where('report_hash', $batch)->delete();
            
            Log::info($insert_row);

        }
        //更新上传表格状态
        DB::table('form_record')->where('id',$data['id'])->update(['status' => 1]);
        return $insert_row;
        
    }

    //格式化输出
    private function valFormat($format, $unit, $found_divisor, $found_divider, $standard_val,&$is_standard)
    {
        $res = '';
        $is_standard = 0;
        switch ($format) {
            case 'default':
                $res = round( ($found_divisor/$found_divider), 3). $unit;
                if (round( ($found_divisor/$found_divider), 3) > $standard_val) {
                    $is_standard = 1;
                }
                break;
            case 'percent':
                $res = round($found_divisor/$found_divider*100,2)."%";
                if (round($found_divisor/$found_divider*100,2)> intval($standard_val)) {
                    $is_standard = 1;
                }
                break;
            case 'scale':
                $res = $this->__ratio($found_divisor, $found_divider);
                $scale = explode(':', $standard_val);
                if (round( ($found_divisor/$found_divider), 3) > round( ($scale[0]/$scale[1]), 3) ) {
                    $is_standard = 1;
                }
                break;

            default:
                # code...
                break;
        }

        return $res;
    }

    private function Object2Array($object) { 
        if (is_object($object) || is_array($object)) {
            $array = array();
            foreach ($object as $key => $value) {
                if ($value == null) continue;
                $array[$key] = self::Object2Array($value);
            }
            return $array;
        }
        else {
            return $object;
        }
    }
    private function __ratio($a, $b) {
        $_a = $a;
        $_b = $b;
        while ($_b != 0) {
            $remainder = $_a % $_b;
            $_a = $_b;
            $_b = $remainder;
        }
        $gcd = abs($_a);
        return ($a / $gcd)  . ':' . ($b / $gcd);

    }

    private function putFilePath($uploadTime, $fileName)
    {
        return'import/' . $uploadTime . $fileName;
    }

    private function check($fileObject)
    {
        $extension = $fileObject->getClientOriginalExtension();
        if($extension != 'xlsx') {
            throw new Exception('只支持Excel文件格式');
        }
    }
}
