<?php

namespace App\Service;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecordService 
{
    public function __construct()
    {
    }

    /**
     * 获取指标报表
     * (new InvoicesExport(2018))->download('invoices.xlsx');
     * @return 
     */
    public function getReports()
    {
        //原始数据
        $sheets = app('db')->select("SELECT * FROM sheet_record");

        return [];
    }
}
