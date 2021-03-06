<?php

namespace App\Http\Controllers;
use App\Events\ExampleEvent;
use App\Jobs\ExampleJob;
use App\Jobs\UploadFileJob;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
class IndexController extends Controller
{
    /**
     * Retrieve the user for the given ID.
     *
     * @param  int  $id
     * @return Response
     */
    public function import()
    {
        // $results = app('db')->select("SELECT * FROM user");
        // dispatch(new UploadFileJob(['id'=>'1','status'=>2]));
        // dispatch(new UploadFileDataJob(['id'=>1,'status'=>2]));
        // event(new ExampleJob(['user'=>'zg']));
        
        return json_encode($results);
    }

    public function export()
    {
        // $results = app('db')->select("SELECT * FROM user");

        // event(new ExampleEvent(['user'=>'zg']));
        $school_report = config('ixport.SCHOOL_REPORT');
        $fileNm = date('Y-m-d H:i:s',time()).'全国义务教育优质均衡县校际均衡情况.xlsx';
        Excel::store(new UsersExport('situation'), $fileNm);

        return json_encode([1]);
    }
}
