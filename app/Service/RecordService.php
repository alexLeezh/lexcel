<?php

namespace App\Service;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;

class RecordService 
{
    //导出表格序号
    private $serial_number = 0;
    //设置老师所在部门单元格,累加器
    private $count_table_list = 0;
    private $date_formate = 'Y年-m月-d日';
    private $teacher_cell  = [];

    public function __construct()
    {
        $this->teacher_cell = config('ixport.TEACHER_CELL_DATAS');
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
        return Excel::download(new UsersExport, 'users.xlsx');
    }
}
