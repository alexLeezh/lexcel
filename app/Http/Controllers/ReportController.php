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
        $result = $this->recordService->getReports();
        return $this->responseData('succ',0, $result);
    }

    /**
     * 生成报表
     * http://localhost:8008/api/v1/report
     * @return 
     */
    public function ls()
    {
        $results = app('db')->select("SELECT * FROM download_record");
        return $this->responseData('succ',0, $data = $results);
    }
}
