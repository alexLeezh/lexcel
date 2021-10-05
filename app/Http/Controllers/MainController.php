<?php

namespace App\Http\Controllers;
use App\Events\ExampleEvent;
use App\Jobs\ExampleJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use app\Providers\UploadServiceProvider;
use App\Http\Responses\DemoResp;
use Illuminate\Http\Request;
use OpenApi\Annotations\Get;
use OpenApi\Annotations\Post;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\RequestBody;
use OpenApi\Annotations\Response;
use OpenApi\Annotations\Schema;

class MainController extends Controller
{
    /**
     * @Post(
     *     path="/upload",
     *     tags={"上传表格"},
     *     summary="上传表格API",
     *     @RequestBody(
     *         @MediaType(
     *             mediaType="application/json",
     *             @Schema(
     *                 required={"name", "age"},
     *                 @Property(property="name", type="string", description="姓名"),
     *                 @Property(property="age", type="integer", description="年龄"),
     *                 @Property(property="gender", type="string", description="性别")
     *             )
     *         )
     *     ),
     *     @Response(
     *         response="200",
     *         description="正常操作响应",
     *         @MediaType(
     *             mediaType="application/json",
     *             @Schema(
     *                 allOf={
     *                     @Schema(ref="#/components/schemas/ApiResponse"),
     *                     @Schema(
     *                         type="object",
     *                         @Property(property="data", ref="#/components/schemas/DemoResp")
     *                     )
     *                 }
     *             )
     *         )
     *     )
     * )
     *
     * @param Request $request
     *
     * @return DemoResp
     */
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

    public function ls(Request $res)
    {
        return response()->json($res);
    }

    public function delete($id)
    {
        $results = app('db')->select("SELECT * FROM user");

        event(new ExampleEvent(['user'=>'zg']));
        return json_encode($results);
    }

    public function generate()
    {
        $results = app('db')->select("SELECT * FROM user");

        event(new ExampleEvent(['user'=>'zg']));
        return json_encode($results);
    }
}
