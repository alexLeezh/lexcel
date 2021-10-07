<?php

namespace App\Exports;

use App\Exports\SchoolePerIndexSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\Log;
use App\Service\RecordService;

class SchoolReportExport implements WithMultipleSheets
{
    use Exportable;

    private $reportNm;
    private $type;
    private $recordService;
    public function __construct($type)
    {
        $this->reportNm = config('ixport.SCHOOL_' . strtoupper($type) . '_REPORT');
        $this->type     = $type;
        $this->recordService = new RecordService();
    }

    /**
     * @return arrays
     */
    public function sheets(): array
    {
        $sheets = [];
        $headings = [];
        foreach ($this->reportNm as $tbk => $tbNm) {

            $headings = config('ixport.SCHOOL_' . strtoupper($this->type) . '_CONFIG')[$tbk];
            $qurey = $this->recordService->__getQuery($this->type, $tbk);
            $sheets[] = new SchoolePerIndexSheet($tbNm, $this->type, $headings, $tbk, $qurey);
        }

        return $sheets;
    }
}
