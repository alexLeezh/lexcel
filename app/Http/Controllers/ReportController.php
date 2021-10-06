<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Service\RecordService;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use App\Service\InvoicesExport;

class ReportController extends Controller
{
    
    
    /** @var $recordService */
    private $recordService;

    /**
     * @param RecordService  $recordService
     */
    public function __construct(RecordService $recordService)
    {
        $this->recordService = new $recordService;
    }

    /**
     * 生成报表
     * http://localhost:8008/api/v1/generate
     * @return 
     */
    public function generate()
    {
        Excel::store(new InvoicesExport(2018), 'invoices.xlsx', 's3');
        // exit;
        // return Excel::download(new UsersExport, 'users.xlsx');

        // $result = $this->recordService->getReports();
        // return $this->responseData('succ',0, $result);
    }
}
