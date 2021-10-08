<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

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

        return response()->download($realPath,$filename,$headers);
    }
}
