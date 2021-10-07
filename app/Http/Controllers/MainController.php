<?php

namespace App\Http\Controllers;
use App\Events\ExampleEvent;
use App\Events\RecordEvent;
use App\Jobs\ExampleJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use app\Providers\UploadServiceProvider;
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
use Exception;

class MainController extends Controller
{
    
    public function up(Request $request)
    {
        $uploadFileService = new UploadServiceProvider();

        $companyId = app('auth')->user()->get('user_id');
        $fileType = $request->input('file_type');
        $fileObject = $request->file('file');
        $shouldQueue = (bool)$request->input("should_queue", 1);

        $distributorId = $request->input('distributor_id', 0);
        $result = $uploadFileService->uploadFile($companyId, $distributorId, $fileType, $fileObject, $shouldQueue);

        return $this->response->array(['data' => $result]);

        //处理队列
        event(new ExampleEvent($request));

        return response()->json($res);
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
