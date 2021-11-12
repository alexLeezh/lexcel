<?php

namespace App\Exports;

use App\Models\User;
use App\Exports\SchoolePerIndexSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\Log;

class UsersExport implements WithMultipleSheets
{
    use Exportable;


    /**
     * @return arrays
     */
    public function sheets(): array
    {
        $sheets = [];
        $headings = [];
        $reportNm = config('ixport.SCHOOL_SITUATION_REPORT');
        foreach ($reportNm as $tbk => $tbNm) {

            $headings = config('ixport.SCHOOL_SITUATION_CONFIG')[$tbk];
            $qurey = $this->qureyData();
            $sheets[] = new SchoolePerIndexSheet($tbNm, 'balance', $headings, $tbk, $qurey);
        }

        return $sheets;
    }

    public function qureyData()
    {
    	$res = [
    		['县市区','小学平均值','5.48','5.49','5.50','5.51','5.52','5.53','5.54','5.55'],
    		['','小学差异系数','0.221','0.222','0.223','0.224','0.225','0.226','0.227','0.228']
    	];
    	return collect(array_values($res));
    }
}