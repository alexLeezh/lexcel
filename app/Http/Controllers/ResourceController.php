<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ResourceController extends Controller
{
    public function index($asset)
    {
        $realPath = urldecode(storage_path('app') .'/'.$asset);
        $filename = urldecode($asset);
        $headers=[
            "Content-Disposition"=>"attachment; filename=".$filename,
            "Content-Transfer-Encoding"=>" binary",
            "Content-Type"=>" application/xls"
        ];

        //下载即可以删除历史数据 truncate 放弃是怕对应问题
        DB::delete('delete from form_record');
        DB::delete('delete from pre_sheet_data');
        DB::delete('delete from sheet_record');
        return response()->download($realPath,$filename,$headers);
    }
}
