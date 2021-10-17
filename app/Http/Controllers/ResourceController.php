<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ResourceController extends Controller
{
    public function index($asset)
    {
        Log::info('ResourceController');
        $realPath = urldecode(storage_path('app') .'/'.$asset);
        $filename = urldecode($asset);
        Log::info($filename);
        Log::info($realPath);
        $headers=[
            "Content-Disposition"=>"attachment; filename=".$filename,
            "Content-Transfer-Encoding"=>"binary",
            "Content-Type"=>"application/xls"
        ];

        // $user =  Auth::getUser();
        
        //下载即可以删除历史数据 truncate 放弃是怕对应问题
        // DB::delete('delete from form_record where user_id = '.$user->id);
        // DB::delete('delete from pre_sheet_data where user_id = '.$user->id);
        // DB::delete('delete from sheet_record where user_id = '.$user->id);
        return response()->download($realPath,$filename,$headers);
    }
}
