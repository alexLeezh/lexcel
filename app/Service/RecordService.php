<?php

namespace App\Service;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use App\Exports\SchoolReportExport;
use App\Events\DownLoadEvent;

class RecordService 
{
    private $school_report  = [];

    public function __construct()
    {
        $this->school_report = config('ixport.SCHOOL_REPORT');
    }

    /**
     * 获取指标报表
     * @return 
     */
    public function getReports()
    {
        $downLoadData = [];
        foreach ($this->school_report as $k => $v) {
            $fileNm = date('Y-m-d H:i:s',time()).$v.'.xlsx';
            Excel::store(new SchoolReportExport($k), $fileNm);
            // $downLoadData[] = ['file_name'=>$v,'file_path'=>storage_path('app') .'/'. $fileNm];
            $downLoadData[] = ['file_name'=>$v,'file_path'=> $fileNm];
        }
        event(new DownLoadEvent($downLoadData));
        return true;
    }

    public function __getQuery($report_type,$school_type)
    {   
        $res = [];
        //原始数据
        $sheetData = app('db')->select("SELECT * FROM sheet_record where report_type = '".$report_type."' and school_type = '".$school_type." ' ");

        switch ($report_type) {
            case 'modern':
                    $res = $this->__getModernQuery($school_type, $sheetData);
                break;
            case 'balance':
                    $res = $this->__getBalanceQuery($school_type, $sheetData);
                break;
            
            default:
                return [];
                break;
        }
        return collect(array_values($res));
    }

    private function __getModernQuery($school_type, $sheetData)
    {
        $res = [];
        switch ($school_type) {
            case 'kindergarten':
                $index = 1;
                foreach ($sheetData as $value) {
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='KCTR'&& $res[$value->school][2] = $value->found_ind=='KCTR' ? $value->found_val : '';
                    $value->found_ind=='KCTR'&& $res[$value->school][3] = $value->found_ind=='KCTR' ? $value->standard_val : '';
                    $value->found_ind=='KCTR'&& $res[$value->school][4] = $value->found_ind=='KCTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    $value->found_ind=='KSTR'&& $res[$value->school][5] = $value->found_ind=='KSTR' ? $value->found_val:'';
                    $value->found_ind=='KSTR'&& $res[$value->school][6] = $value->found_ind=='KSTR' ? $value->standard_val:'';
                    $value->found_ind=='KSTR'&& $res[$value->school][7] = $value->found_ind=='KSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    $index++;
                }
                break;
            case 'primarySchool':
                $index = 1;
                foreach ($sheetData as $value) {
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='PSTR' && $res[$value->school][2] = $value->found_ind=='PSTR' ? $value->found_val:'';
                    $value->found_ind=='PSTR' && $res[$value->school][3] = $value->found_ind=='PSTR' ? $value->standard_val:'';
                    $value->found_ind=='PSTR' && $res[$value->school][4] = $value->found_ind=='PSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    $value->found_ind=='PTR' && $res[$value->school][5] = $value->found_ind=='PTR' ? $value->found_val:'';
                    $value->found_ind=='PTR' && $res[$value->school][6] = $value->found_ind=='PTR' ? $value->standard_val:'';
                    $value->found_ind=='PTR' && $res[$value->school][7] = $value->found_ind=='PTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PFCR' && $res[$value->school][8] = $value->found_ind=='PFCR' ? $value->found_val:'';
                    $value->found_ind=='PFCR' && $res[$value->school][9] = $value->found_ind=='PFCR' ? $value->standard_val:'';
                    $value->found_ind=='PFCR' && $res[$value->school][10] = $value->found_ind=='PFCR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PHSTR' && $res[$value->school][11] = $value->found_ind=='PHSTR' ? $value->found_val:'';
                    $value->found_ind=='PHSTR' && $res[$value->school][12] = $value->found_ind=='PHSTR' ? $value->standard_val:'';
                    $value->found_ind=='PHSTR' && $res[$value->school][13] = $value->found_ind=='PHSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PSBR' && $res[$value->school][14] = $value->found_ind=='PSBR' ? $value->found_val:'';
                    $value->found_ind=='PSBR' && $res[$value->school][15] = $value->found_ind=='PSBR' ? $value->standard_val:'';
                    $value->found_ind=='PSBR' && $res[$value->school][16] = $value->found_ind=='PSBR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $index++;
                }
                break;
            case 'juniorMiddleSchool':
                $index = 1;
                foreach ($sheetData as $value) {
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='JSTR' && $res[$value->school][2] = $value->found_ind=='JSTR' ? $value->found_val:'';
                    $value->found_ind=='JSTR' && $res[$value->school][3] = $value->found_ind=='JSTR' ? $value->standard_val:'';
                    $value->found_ind=='JSTR' && $res[$value->school][4] = $value->found_ind=='JSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    $value->found_ind=='JETR' && $res[$value->school][5] = $value->found_ind=='JETR' ? $value->found_val:'';
                    $value->found_ind=='JETR' && $res[$value->school][6] = $value->found_ind=='JETR' ? $value->standard_val:'';
                    $value->found_ind=='JETR' && $res[$value->school][7] = $value->found_ind=='JETR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JFCR' && $res[$value->school][8] = $value->found_ind=='JFCR' ? $value->found_val:'';
                    $value->found_ind=='JFCR' && $res[$value->school][9] = $value->found_ind=='JFCR' ? $value->standard_val:'';
                    $value->found_ind=='JFCR' && $res[$value->school][10] = $value->found_ind=='JFCR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JHSTR' && $res[$value->school][11] = $value->found_ind=='JHSTR' ? $value->found_val:'';
                    $value->found_ind=='JHSTR' && $res[$value->school][12] = $value->found_ind=='JHSTR' ? $value->standard_val:'';
                    $value->found_ind=='JHSTR' && $res[$value->school][13] = $value->found_ind=='JHSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JSBR' && $res[$value->school][14] = $value->found_ind=='JSBR' ? $value->found_val:'';
                    $value->found_ind=='JSBR' && $res[$value->school][15] = $value->found_ind=='JSBR' ? $value->standard_val:'';
                    $value->found_ind=='JSBR' && $res[$value->school][16] = $value->found_ind=='JSBR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $index++;
                }
                break;
            case 'highSchool':
                $index = 1;
                foreach ($sheetData as $value) {
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='HSTR' && $res[$value->school][2] = $value->found_ind=='HSTR' ? $value->found_val:'';
                    $value->found_ind=='HSTR' && $res[$value->school][3] = $value->found_ind=='HSTR' ? $value->standard_val:'';
                    $value->found_ind=='HSTR' && $res[$value->school][4] = $value->found_ind=='HSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    $value->found_ind=='HETR' && $res[$value->school][5] = $value->found_ind=='HETR' ? $value->found_val:'';
                    $value->found_ind=='HETR' && $res[$value->school][6] = $value->found_ind=='HETR' ? $value->standard_val:'';
                    $value->found_ind=='HETR' && $res[$value->school][7] = $value->found_ind=='HETR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='HSMR' && $res[$value->school][8] = $value->found_ind=='HSMR' ? $value->found_val:'';
                    $value->found_ind=='HSMR' && $res[$value->school][9] = $value->found_ind=='HSMR' ? $value->standard_val:'';
                    $value->found_ind=='HSMR' && $res[$value->school][10] = $value->found_ind=='HSMR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $index++;
                }
                break;
            case 'secondaryVocationalSchool':
                $index = 1;
                foreach ($sheetData as $value) {
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;

                    $value->found_ind=='VSTR' && $res[$value->school][2] = $value->found_ind=='VSTR' ? $value->found_val:'';
                    $value->found_ind=='VSTR' && $res[$value->school][3] = $value->found_ind=='VSTR' ? $value->standard_val:'';
                    $value->found_ind=='VSTR' && $res[$value->school][4] = $value->found_ind=='VSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    $value->found_ind=='VETR' && $res[$value->school][5] = $value->found_ind=='VETR' ? $value->found_val:'';
                    $value->found_ind=='VETR' && $res[$value->school][6] = $value->found_ind=='VETR' ? $value->standard_val:'';
                    $value->found_ind=='VETR' && $res[$value->school][7] = $value->found_ind=='VETR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='VSMR' && $res[$value->school][8] = $value->found_ind=='VSMR' ? $value->found_val:'';
                    $value->found_ind=='VSMR' && $res[$value->school][9] = $value->found_ind=='VSMR' ? $value->standard_val:'';
                    $value->found_ind=='VSMR' && $res[$value->school][10] = $value->found_ind=='VSMR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $index++;
                }
                break;    
            case 'specialSchool':
                $index = 1;
                foreach ($sheetData as $value) {
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='SSTR' && $res[$value->school][2] = $value->found_ind=='SSTR' ? $value->found_val:'';
                    $value->found_ind=='SSTR' && $res[$value->school][3] = $value->found_ind=='SSTR' ? $value->standard_val:'';
                    $value->found_ind=='SSTR' && $res[$value->school][4] = $value->found_ind=='SSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $index++;
                }
                break;
            default:
                return [];
                break;
        }
        return $res;
    }

    private function __getBalanceQuery($school_type, $sheetData)
    {
        switch ($school_type) {
            case 'primarySchool':
                $index = 1;
                foreach ($sheetData as $value) {
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='PHETR' && $res[$value->school][2] = $value->found_ind=='PHETR' ? $value->basic_val:'';
                    $value->found_ind=='PHETR' && $res[$value->school][3] = $value->found_ind=='PHETR' ? $value->standard_val:'';
                    $value->found_ind=='PHETR' && $res[$value->school][4] = $value->found_ind=='PHETR' ? $value->found_val:'';
                    $value->found_ind=='PHETR' && $res[$value->school][5] = $value->found_ind=='PHETR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PHBTR' && $res[$value->school][6] = $value->found_ind=='PHBTR' ? $value->basic_val:'';
                    $value->found_ind=='PHBTR' && $res[$value->school][7] = $value->found_ind=='PHBTR' ? $value->standard_val:'';
                    $value->found_ind=='PHBTR' && $res[$value->school][8] = $value->found_ind=='PHBTR' ? $value->found_val:'';
                    $value->found_ind=='PHBTR' && $res[$value->school][9] = $value->found_ind=='PHBTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PHATR' && $res[$value->school][10] = $value->found_ind=='PHATR' ? $value->basic_val:'';
                    $value->found_ind=='PHATR' && $res[$value->school][11] = $value->found_ind=='PHATR' ? $value->standard_val:'';
                    $value->found_ind=='PHATR' && $res[$value->school][12] = $value->found_ind=='PHATR' ? $value->found_val:'';
                    $value->found_ind=='PHATR' && $res[$value->school][13] = $value->found_ind=='PHATR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PSRAR' && $res[$value->school][14] = $value->found_ind=='PSRAR' ? $value->basic_val:'';
                    $value->found_ind=='PSRAR' && $res[$value->school][15] = $value->found_ind=='PSRAR' ? $value->standard_val:'';
                    $value->found_ind=='PSRAR' && $res[$value->school][16] = $value->found_ind=='PSRAR' ? $value->found_val:'';
                    $value->found_ind=='PSRAR' && $res[$value->school][17] = $value->found_ind=='PSRAR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PSMAR' && $res[$value->school][18] = $value->found_ind=='PSMAR' ? $value->basic_val:'';
                    $value->found_ind=='PSMAR' && $res[$value->school][19] = $value->found_ind=='PSMAR' ? $value->standard_val:'';
                    $value->found_ind=='PSMAR' && $res[$value->school][20] = $value->found_ind=='PSMAR' ? $value->found_val:'';
                    $value->found_ind=='PSMAR' && $res[$value->school][21] = $value->found_ind=='PSMAR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PSMR' && $res[$value->school][22] = $value->found_ind=='PSMR' ? $value->basic_val:'';
                    $value->found_ind=='PSMR' && $res[$value->school][23] = $value->found_ind=='PSMR' ? $value->standard_val:'';
                    $value->found_ind=='PSMR' && $res[$value->school][24] = $value->found_ind=='PSMR' ? $value->found_val:'';
                    $value->found_ind=='PSMR' && $res[$value->school][25] = $value->found_ind=='PSMR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PHIR' && $res[$value->school][26] = $value->found_ind=='PHIR' ? $value->basic_val:'';
                    $value->found_ind=='PHIR' && $res[$value->school][27] = $value->found_ind=='PHIR' ? $value->standard_val:'';
                    $value->found_ind=='PHIR' && $res[$value->school][28] = $value->found_ind=='PHIR' ? $value->found_val:'';
                    $value->found_ind=='PHIR' && $res[$value->school][29] = $value->found_ind=='PHIR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    

                    $index++;
                }
                break;
            case 'juniorMiddleSchool':
                $index = 1;
                foreach ($sheetData as $value) {
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='JHETR' && $res[$value->school][2] = $value->found_ind=='JHETR' ? $value->basic_val:'';
                    $value->found_ind=='JHETR' && $res[$value->school][3] = $value->found_ind=='JHETR' ? $value->standard_val:'';
                    $value->found_ind=='JHETR' && $res[$value->school][4] = $value->found_ind=='JHETR' ? $value->found_val:'';
                    $value->found_ind=='JHETR' && $res[$value->school][5] = $value->found_ind=='JHETR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JHBTR' && $res[$value->school][6] = $value->found_ind=='JHBTR' ? $value->basic_val:'';
                    $value->found_ind=='JHBTR' && $res[$value->school][7] = $value->found_ind=='JHBTR' ? $value->standard_val:'';
                    $value->found_ind=='JHBTR' && $res[$value->school][8] = $value->found_ind=='JHBTR' ? $value->found_val:'';
                    $value->found_ind=='JHBTR' && $res[$value->school][9] = $value->found_ind=='JHBTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JHATR' && $res[$value->school][10] = $value->found_ind=='JHATR' ? $value->basic_val:'';
                    $value->found_ind=='JHATR' && $res[$value->school][11] = $value->found_ind=='JHATR' ? $value->standard_val:'';
                    $value->found_ind=='JHATR' && $res[$value->school][12] = $value->found_ind=='JHATR' ? $value->found_val:'';
                    $value->found_ind=='JHATR' && $res[$value->school][13] = $value->found_ind=='JHATR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JSRAR' && $res[$value->school][14] = $value->found_ind=='JSRAR' ? $value->basic_val:'';
                    $value->found_ind=='JSRAR' && $res[$value->school][15] = $value->found_ind=='JSRAR' ? $value->standard_val:'';
                    $value->found_ind=='JSRAR' && $res[$value->school][16] = $value->found_ind=='JSRAR' ? $value->found_val:'';
                    $value->found_ind=='JSRAR' && $res[$value->school][17] = $value->found_ind=='JSRAR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JSMAR' && $res[$value->school][18] = $value->found_ind=='JSMAR' ? $value->basic_val:'';
                    $value->found_ind=='JSMAR' && $res[$value->school][19] = $value->found_ind=='JSMAR' ? $value->standard_val:'';
                    $value->found_ind=='JSMAR' && $res[$value->school][20] = $value->found_ind=='JSMAR' ? $value->found_val:'';
                    $value->found_ind=='JSMAR' && $res[$value->school][21] = $value->found_ind=='JSMAR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JSMR' && $res[$value->school][22] = $value->found_ind=='JSMR' ? $value->basic_val:'';
                    $value->found_ind=='JSMR' && $res[$value->school][23] = $value->found_ind=='JSMR' ? $value->standard_val:'';
                    $value->found_ind=='JSMR' && $res[$value->school][24] = $value->found_ind=='JSMR' ? $value->found_val:'';
                    $value->found_ind=='JSMR' && $res[$value->school][25] = $value->found_ind=='JSMR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JHIR' && $res[$value->school][26] = $value->found_ind=='JHIR' ? $value->basic_val:'';
                    $value->found_ind=='JHIR' && $res[$value->school][27] = $value->found_ind=='JHIR' ? $value->standard_val:'';
                    $value->found_ind=='JHIR' && $res[$value->school][28] = $value->found_ind=='JHIR' ? $value->found_val:'';
                    $value->found_ind=='JHIR' && $res[$value->school][29] = $value->found_ind=='JHIR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    

                    $index++;
                }
                break;
            case 'nineYearCon':
                $index = 1;
                foreach ($sheetData as $value) {
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='NHETR' && $res[$value->school][2] = $value->found_ind=='NHETR' ? $value->basic_val:'';
                    $value->found_ind=='NHETR' && $res[$value->school][3] = $value->found_ind=='NHETR' ? $value->standard_val:'';
                    $value->found_ind=='NHETR' && $res[$value->school][4] = $value->found_ind=='NHETR' ? $value->found_val:'';
                    $value->found_ind=='NHETR' && $res[$value->school][5] = $value->found_ind=='NHETR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='NHBTR' && $res[$value->school][6] = $value->found_ind=='NHBTR' ? $value->basic_val:'';
                    $value->found_ind=='NHBTR' && $res[$value->school][7] = $value->found_ind=='NHBTR' ? $value->standard_val:'';
                    $value->found_ind=='NHBTR' && $res[$value->school][8] = $value->found_ind=='NHBTR' ? $value->found_val:'';
                    $value->found_ind=='NHBTR' && $res[$value->school][9] = $value->found_ind=='NHBTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='NSATR' && $res[$value->school][10] = $value->found_ind=='NSATR' ? $value->basic_val:'';
                    $value->found_ind=='NSATR' && $res[$value->school][11] = $value->found_ind=='NSATR' ? $value->standard_val:'';
                    $value->found_ind=='NSATR' && $res[$value->school][12] = $value->found_ind=='NSATR' ? $value->found_val:'';
                    $value->found_ind=='NSATR' && $res[$value->school][13] = $value->found_ind=='NSATR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='NSRAR' && $res[$value->school][14] = $value->found_ind=='NSRAR' ? $value->basic_val:'';
                    $value->found_ind=='NSRAR' && $res[$value->school][15] = $value->found_ind=='NSRAR' ? $value->standard_val:'';
                    $value->found_ind=='NSRAR' && $res[$value->school][16] = $value->found_ind=='NSRAR' ? $value->found_val:'';
                    $value->found_ind=='NSRAR' && $res[$value->school][17] = $value->found_ind=='NSRAR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='NSMAR' && $res[$value->school][18] = $value->found_ind=='NSMAR' ? $value->basic_val:'';
                    $value->found_ind=='NSMAR' && $res[$value->school][19] = $value->found_ind=='NSMAR' ? $value->standard_val:'';
                    $value->found_ind=='NSMAR' && $res[$value->school][20] = $value->found_ind=='NSMAR' ? $value->found_val:'';
                    $value->found_ind=='NSMAR' && $res[$value->school][21] = $value->found_ind=='NSMAR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='NSMR' && $res[$value->school][22] = $value->found_ind=='NSMR' ? $value->basic_val:'';
                    $value->found_ind=='NSMR' && $res[$value->school][23] = $value->found_ind=='NSMR' ? $value->standard_val:'';
                    $value->found_ind=='NSMR' && $res[$value->school][24] = $value->found_ind=='NSMR' ? $value->found_val:'';
                    $value->found_ind=='NSMR' && $res[$value->school][25] = $value->found_ind=='NSMR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='NHIR' && $res[$value->school][26] = $value->found_ind=='NHIR' ? $value->basic_val:'';
                    $value->found_ind=='NHIR' && $res[$value->school][27] = $value->found_ind=='NHIR' ? $value->standard_val:'';
                    $value->found_ind=='NHIR' && $res[$value->school][28] = $value->found_ind=='NHIR' ? $value->found_val:'';
                    $value->found_ind=='NHIR' && $res[$value->school][29] = $value->found_ind=='NHIR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    

                    $index++;
                }
                break;
            default:
                return [];
                break;
        }
        return $res;
    }

}
