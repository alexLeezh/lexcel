<?php

namespace App\Http\Controllers;
use App\Events\ExampleEvent;
use App\Events\RecordEvent;
use App\Jobs\ExampleJob;
use App\Jobs\UploadFileJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Responses\DemoResp;
use OpenApi\Annotations\Get;
use OpenApi\Annotations\Post;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\RequestBody;
use OpenApi\Annotations\Response;
use OpenApi\Annotations\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Service\UploadService;

use Exception;

class MainController extends Controller
{
    
    /**
     * 上传文件
     * http://localhost:8008/api/v1/upload
     * @param  Request $request 
     * @return           
     */
    public function up(Request $request)
    {
        //添加队列
        $fileObject = $request->file('file');
        // Log::info($fileObject);
        // dispatch(new UploadFileJob(['fileObject'=>$fileObject]));
        // return $this->responseData('加入处理队列，等待处理',0, $data = null);
        $uploadFileService = new UploadService();
        try {
            $result = $uploadFileService->uploadFile($fileObject, $msg);
            if (!$result) {
                return $this->responseData($msg,1, $data = null);
            }
        } catch (Exception $e) {
            return $this->responseData($e->getMessage(),1, $data = null);
        }
        
        return $this->responseData('succ',0, $data = null);
    }

    /**
     * 返回上传表格记录 Authorization Bearer 
     * http://localhost:8008/api/v1/uplist
     * @param  Request $res 
     * @return        
     */
    public function ls(Request $res)
    {
        $results = app('db')->select("SELECT * FROM form_record");
        $arr = [];
        foreach ($results as &$value) {
            if (in_array($value->form_name, $arr)) {
                // $value->form_name = $value->form_name;
                $value->form_name_sign = 1;
            }else{
                $value->form_name_sign = 0;
                array_push($arr, $value->form_name);
            }
        }
        return $this->responseData('succ',0, $data = $results);
    }

    /**
     * 上传记录删除 Authorization Bearer 
     * http://localhost:8008/api/v1/del/1
     * @param  [int] $id 
     * @return [boole]
     */
    public function delete($id)
    {

        DB::beginTransaction();
        try {
            $deleted = DB::table('form_record')->delete(['id' => $id]);
            event(new RecordEvent(['form_id'=>$id]));
        } catch (Exception $e) {
            DB::rollBack();
            return $this->responseData('error',1, $data = null);
        }
        DB::commit();
       
        return $this->responseData('succ',0, $data = null);
    }


}
