<?php

namespace App\Service;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use App\Exports\SchoolReportExport;
use App\Events\DownLoadEvent;
use Illuminate\Support\Facades\Auth;

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
        $user =  Auth::getUser();
        //判断是否还有数据
        if (!app('db')->select("SELECT * FROM sheet_record where  user_id = '".$user->id." ' ")) {
            return true;
        }
        foreach ($this->school_report as $k => $v) {
            $fileNm = date('Y-m-d H:i:s',time()).$v.'.xlsx';
            Excel::store(new SchoolReportExport($k), $fileNm);
            // $downLoadData[] = ['file_name'=>$v,'file_path'=>storage_path('app') .'/'. $fileNm];
            $downLoadData[] = ['file_name'=>$v,'file_path'=> $fileNm, 'user_id' =>$user->id];
        }

        event(new DownLoadEvent($downLoadData));
        return true;
    }

    public function __getQuery($report_type,$school_type)
    {   
        $res = [];
        //原始数据
        $user =  Auth::getUser();
        $user_id = $user->id;
        $sheetData = app('db')->select("SELECT * FROM sheet_record where report_type = '".$report_type."' and school_type = '".$school_type." ' and user_id = '".$user_id." ' ");

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
        $global_config = config('ixport.SCHOOL_IMPORT_FOUND_INDEX');
        switch ($school_type) {
            case 'kindergarten':
                $index = 1;
                $sumKCTR = 0;
                $sumKSTR = 0;
                foreach ($sheetData as $value) {
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='KCTR'&& $res[$value->school][2] = $value->found_ind=='KCTR' ? $value->found_val : '';
                    $value->found_ind=='KCTR'&& $res[$value->school][3] = $value->found_ind=='KCTR' ? $value->standard_val : '';
                    $value->found_ind=='KCTR'&& $res[$value->school][4] = $value->found_ind=='KCTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    $value->found_ind=='KSTR'&& $res[$value->school][5] = $value->found_ind=='KSTR' ? $value->found_val:'';
                    $value->found_ind=='KSTR'&& $res[$value->school][6] = $value->found_ind=='KSTR' ? $value->standard_val:'';
                    $value->found_ind=='KSTR'&& $res[$value->school][7] = $value->found_ind=='KSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    if ($value->found_ind=='KCTR' && ($global_config['KCTR']['ratio']=='default' || $global_config['KCTR']['ratio']=='percent')) {
                        $kctr = intval($value->found_val);
                    }
                    if ($value->found_ind=='KCTR' && $global_config['KCTR']['ratio']=='scale') {
                        $kctr = explode(':', $value->found_val)[0];
                    }

                    if ($value->found_ind=='KSTR' && ($global_config['KSTR']['ratio']=='default' || $global_config['KSTR']['ratio']=='percent')) {
                        $kstr = intval($value->found_val);
                    }
                    if ($value->found_ind=='KSTR' && $global_config['KSTR']['ratio']=='scale') {
                        $kstr = explode(':', $value->found_val)[0];
                    }

                    ($value->found_ind=='KCTR'&& $value->found_val) && $sumKCTR += $kctr;
                    ($value->found_ind=='KSTR'&& $value->found_val) && $sumKSTR += $kstr;
                    $index++;
                }
                if (!$res) {
                    break;
                }
                $count = count($res);
                //合计
                $res['合计'][0] = '';
                $res['合计'][1] = '合计';
                $res['合计'][2] = $global_config['KCTR']['ratio']=='default'? $sumKCTR.$global_config['KCTR']['unit']:($global_config['KCTR']['ratio']=='percent'?round( ($sumKCTR/$count), 3).'%':round( ($sumKCTR/$count), 3).':1');
                $res['合计'][3] = $global_config['KCTR']['standard_val'];
                $res['合计'][4] = '合计';

                $res['合计'][5] = $global_config['KSTR']['ratio']=='default'? $sumKSTR.$global_config['KSTR']['unit']:($global_config['KSTR']['ratio']=='percent'?round( ($sumKSTR/$count), 3).'%':round( ($sumKSTR/$count), 3).':1');
                $res['合计'][6] = $global_config['KSTR']['standard_val'];
                $res['合计'][7] = '合计';

                break;
            case 'primarySchool':
                $index = 1;
                $sumPSTR = 0;
                $sumPTR = 0;
                $sumPHSTR = 0;
                $sumPFCR = 0;
                $sumPSBR = 0;
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

                    //------PSTR
                    if ($value->found_ind=='PSTR' && ($global_config['PSTR']['ratio']=='default' || $global_config['PSTR']['ratio']=='percent')) {
                        $pstr = intval($value->found_val);
                    }
                    if ($value->found_ind=='PSTR' && $global_config['PSTR']['ratio']=='scale') {
                        $pstr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='PSTR'&& $value->found_val) && $sumPSTR += $pstr;
                    //---PSTR
                    //
                    //PTR
                    if ($value->found_ind=='PTR' && ($global_config['PTR']['ratio']=='default' || $global_config['PTR']['ratio']=='percent')) {
                        $ptr = intval($value->found_val);
                    }
                    if ($value->found_ind=='PTR' && $global_config['PTR']['ratio']=='scale') {
                        $ptr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='PTR'&& $value->found_val) && $sumPTR += $ptr;
                    //---PTR
                    
                    //---PFCR
                    if ($value->found_ind=='PFCR' && ($global_config['PFCR']['ratio']=='default' || $global_config['PFCR']['ratio']=='percent')) {
                        $pfcr = intval($value->found_val);
                    }
                    if ($value->found_ind=='PFCR' && $global_config['PFCR']['ratio']=='scale') {
                        $pfcr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='PFCR'&& $value->found_val) && $sumPFCR += $pfcr;
                    //-----PFCR

                    //-------PHSTR
                    if ($value->found_ind=='PHSTR' && ($global_config['PHSTR']['ratio']=='default' || $global_config['PHSTR']['ratio']=='percent')) {
                        $phstr = intval($value->found_val);
                    }
                    if ($value->found_ind=='PHSTR' && $global_config['PHSTR']['ratio']=='scale') {
                        $phstr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='PHSTR'&& $value->found_val) && $sumPHSTR += $phstr;
                    //---PHSTR
                    
                    //-------PSBR
                    if ($value->found_ind=='PSBR' && ($global_config['PSBR']['ratio']=='default' || $global_config['PSBR']['ratio']=='percent')) {
                        $psbr = intval($value->found_val);
                    }
                    if ($value->found_ind=='PSBR' && $global_config['PSBR']['ratio']=='scale') {
                        $psbr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='PSBR'&& $value->found_val) && $sumPSBR += $psbr;
                    //---PSBR
                    $index++;
                }
                if (!$res) {
                    break;
                }
                $count = count($res);
                //合计
                $res['合计'][0] = '';
                $res['合计'][1] = '合计';
                $res['合计'][2] = $global_config['PSTR']['ratio']=='default'? $sumPSTR.$global_config['PSTR']['unit']:($global_config['PSTR']['ratio']=='percent'?round( ($sumPSTR/$count), 3).'%':round( ($sumPSTR/$count), 3).':1');
                $res['合计'][3] = $global_config['PSTR']['standard_val'];
                $res['合计'][4] = '合计';

                $res['合计'][5] = $global_config['PTR']['ratio']=='default'? $sumPTR.$global_config['PTR']['unit']:($global_config['PTR']['ratio']=='percent'?round( ($sumPTR/$count), 3).'%':round( ($sumPTR/$count), 3).':1');;
                $res['合计'][6] = $global_config['PTR']['standard_val'];
                $res['合计'][7] = '合计';

                $res['合计'][8] = $global_config['PFCR']['ratio']=='default'? $sumPFCR.$global_config['PFCR']['unit']:($global_config['PFCR']['ratio']=='percent'?round( ($sumPFCR/$count), 3).'%':round( ($sumPFCR/$count), 3).':1');;
                $res['合计'][9] = $global_config['PFCR']['standard_val'];
                $res['合计'][10] = '合计';

                $res['合计'][11] = $global_config['PHSTR']['ratio']=='default'? $sumPHSTR.$global_config['PHSTR']['unit']:($global_config['PHSTR']['ratio']=='percent'?round( ($sumPHSTR/$count), 3).'%':round( ($sumPHSTR/$count), 3).':1');;
                $res['合计'][12] = $global_config['PHSTR']['standard_val'];
                $res['合计'][13] = '合计';

                $res['合计'][14] = $global_config['PSBR']['ratio']=='default'? $sumPSBR.$global_config['PSBR']['unit']:($global_config['PSBR']['ratio']=='percent'?round( ($sumPSBR/$count), 3).'%':round( ($sumPSBR/$count), 3).':1');;
                $res['合计'][15] = $global_config['PSBR']['standard_val'];
                $res['合计'][16] = '合计';
                
                break;
            case 'juniorMiddleSchool':
                $index = 1;
                $sumJSTR = 0;
                $sumJETR = 0;
                $sumJFCR = 0;
                $sumJHSTR = 0;
                $sumJSBR = 0;
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

                    //------JSTR
                    if ($value->found_ind=='JSTR' && ($global_config['JSTR']['ratio']=='default' || $global_config['JSTR']['ratio']=='percent')) {
                        $jstr = intval($value->found_val);
                    }
                    if ($value->found_ind=='JSTR' && $global_config['JSTR']['ratio']=='scale') {
                        $jstr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='JSTR'&& $value->found_val) && $sumJSTR += $jstr;
                    //---JSTR
                    //
                    //JETR
                    if ($value->found_ind=='JETR' && ($global_config['JETR']['ratio']=='default' || $global_config['JETR']['ratio']=='percent')) {
                        $jetr = intval($value->found_val);
                    }
                    if ($value->found_ind=='JETR' && $global_config['JETR']['ratio']=='scale') {
                        $jetr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='JETR'&& $value->found_val) && $sumJETR += $jetr;
                    //---JETR
                    
                    //---JFCR
                    if ($value->found_ind=='JFCR' && ($global_config['JFCR']['ratio']=='default' || $global_config['JFCR']['ratio']=='percent')) {
                        $jfcr = intval($value->found_val);
                    }
                    if ($value->found_ind=='JFCR' && $global_config['JFCR']['ratio']=='scale') {
                        $jfcr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='JFCR'&& $value->found_val) && $sumJFCR += $jfcr;
                    //-----JFCR

                    //-------JHSTR
                    if ($value->found_ind=='JHSTR' && ($global_config['JHSTR']['ratio']=='default' || $global_config['JHSTR']['ratio']=='percent')) {
                        $jhstr = intval($value->found_val);
                    }
                    if ($value->found_ind=='JHSTR' && $global_config['JHSTR']['ratio']=='scale') {
                        $jhstr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='JHSTR'&& $value->found_val) && $sumJHSTR += $jhstr;
                    //---JHSTR
                    
                    //-------JSBR
                    if ($value->found_ind=='JSBR' && ($global_config['JSBR']['ratio']=='default' || $global_config['JSBR']['ratio']=='percent')) {
                        $jsbr = intval($value->found_val);
                    }
                    if ($value->found_ind=='JSBR' && $global_config['JSBR']['ratio']=='scale') {
                        $jsbr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='JSBR'&& $value->found_val) && $sumJSBR += $jsbr;
                    //---JSBR
                    $index++;
                }
                if (!$res) {
                    break;
                }
                $count = count($res);
                //合计
                $res['合计'][0] = '';
                $res['合计'][1] = '合计';
                $res['合计'][2] = $global_config['JSTR']['ratio']=='default'? $sumJSTR.$global_config['JSTR']['unit']:($global_config['JSTR']['ratio']=='percent'?round( ($sumJSTR/$count), 3).'%':round( ($sumJSTR/$count), 3).':1');
                $res['合计'][3] = $global_config['JSTR']['standard_val'];
                $res['合计'][4] = '合计';

                $res['合计'][5] = $global_config['JETR']['ratio']=='default'? $sumJETR.$global_config['JETR']['unit']:($global_config['JETR']['ratio']=='percent'?round( ($sumJETR/$count), 3).'%':round( ($sumJETR/$count), 3).':1');;
                $res['合计'][6] = $global_config['JETR']['standard_val'];
                $res['合计'][7] = '合计';

                $res['合计'][8] = $global_config['JFCR']['ratio']=='default'? $sumJFCR.$global_config['JFCR']['unit']:($global_config['JFCR']['ratio']=='percent'?round( ($sumJFCR/$count), 3).'%':round( ($sumJFCR/$count), 3).':1');;
                $res['合计'][9] = $global_config['JFCR']['standard_val'];
                $res['合计'][10] = '合计';

                $res['合计'][11] = $global_config['JHSTR']['ratio']=='default'? $sumJHSTR.$global_config['JHSTR']['unit']:($global_config['JHSTR']['ratio']=='percent'?round( ($sumJHSTR/$count), 3).'%':round( ($sumJHSTR/$count), 3).':1');;
                $res['合计'][12] = $global_config['JHSTR']['standard_val'];
                $res['合计'][13] = '合计';

                $res['合计'][14] = $global_config['JSBR']['ratio']=='default'? $sumJSBR.$global_config['JSBR']['unit']:($global_config['JSBR']['ratio']=='percent'?round( ($sumJSBR/$count), 3).'%':round( ($sumJSBR/$count), 3).':1');;
                $res['合计'][15] = $global_config['JSBR']['standard_val'];
                $res['合计'][16] = '合计';
                break;
            case 'highSchool':
                $index = 1;
                $sumHSTR = 0;
                $sumHETR = 0;
                $sumHSMR = 0;
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

                    //------HSTR
                    if ($value->found_ind=='HSTR' && ($global_config['HSTR']['ratio']=='default' || $global_config['HSTR']['ratio']=='percent')) {
                        $hstr = intval($value->found_val);
                    }
                    if ($value->found_ind=='HSTR' && $global_config['HSTR']['ratio']=='scale') {
                        $hstr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='HSTR'&& $value->found_val) && $sumHSTR += $hstr;
                    //---HSTR
                    //
                    //HETR
                    if ($value->found_ind=='HETR' && ($global_config['HETR']['ratio']=='default' || $global_config['HETR']['ratio']=='percent')) {
                        $hetr = intval($value->found_val);
                    }
                    if ($value->found_ind=='HETR' && $global_config['HETR']['ratio']=='scale') {
                        $hetr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='HETR'&& $value->found_val) && $sumHETR += $hetr;
                    //---HETR
                    
                    //---HSMR
                    if ($value->found_ind=='HSMR' && ($global_config['HSMR']['ratio']=='default' || $global_config['HSMR']['ratio']=='percent')) {
                        $hsmr = intval($value->found_val);
                    }
                    if ($value->found_ind=='HSMR' && $global_config['HSMR']['ratio']=='scale') {
                        $hsmr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='HSMR'&& $value->found_val) && $sumHSMR += $hsmr;
                    //-----HSMR
                    $index++;
                }

                if (!$res) {
                    break;
                }
                $count = count($res);
                $res['合计'][0] = '';
                $res['合计'][1] = '合计';
                $res['合计'][2] = $global_config['HSTR']['ratio']=='default'? $sumHSTR.$global_config['HSTR']['unit']:($global_config['HSTR']['ratio']=='percent'?round( ($sumHSTR/$count), 3).'%':round( ($sumHSTR/$count), 3).':1');
                $res['合计'][3] = $global_config['HSTR']['standard_val'];
                $res['合计'][4] = '合计';

                $res['合计'][5] = $global_config['HETR']['ratio']=='default'? $sumHETR.$global_config['HETR']['unit']:($global_config['HETR']['ratio']=='percent'?round( ($sumHETR/$count), 3).'%':round( ($sumHETR/$count), 3).':1');;
                $res['合计'][6] = $global_config['HETR']['standard_val'];
                $res['合计'][7] = '合计';

                $res['合计'][8] = $global_config['HSMR']['ratio']=='default'? $sumHSMR.$global_config['HSMR']['unit']:($global_config['HSMR']['ratio']=='percent'?round( ($sumHSMR/$count), 3).'%':round( ($sumHSMR/$count), 3).':1');;
                $res['合计'][9] = $global_config['HSMR']['standard_val'];
                $res['合计'][10] = '合计';

                break;
            case 'secondaryVocationalSchool':
                $index = 1;
                $sumVETR = 0;
                $sumVSTR = 0;
                $sumVSMR = 0;
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

                    //------VSTR
                    if ($value->found_ind=='VSTR' && ($global_config['VSTR']['ratio']=='default' || $global_config['VSTR']['ratio']=='percent')) {
                        $vstr = intval($value->found_val);
                    }
                    if ($value->found_ind=='VSTR' && $global_config['VSTR']['ratio']=='scale') {
                        $vstr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='VSTR'&& $value->found_val) && $sumVSTR += $vstr;
                    //---VSTR
                    //
                    //VETR
                    if ($value->found_ind=='VETR' && ($global_config['VETR']['ratio']=='default' || $global_config['VETR']['ratio']=='percent')) {
                        $vetr = intval($value->found_val);
                    }
                    if ($value->found_ind=='VETR' && $global_config['VETR']['ratio']=='scale') {
                        $vetr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='VETR'&& $value->found_val) && $sumVETR += $vetr;
                    //---VETR
                    
                    //---VSMR
                    if ($value->found_ind=='VSMR' && ($global_config['VSMR']['ratio']=='default' || $global_config['VSMR']['ratio']=='percent')) {
                        $vsmr = intval($value->found_val);
                    }
                    if ($value->found_ind=='VSMR' && $global_config['VSMR']['ratio']=='scale') {
                        $vsmr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='VSMR'&& $value->found_val) && $sumVSMR += $vsmr;
                    //-----VSMR
                    $index++;
                }

                if (!$res) {
                    break;
                }
                $count = count($res);
                $res['合计'][0] = '';
                $res['合计'][1] = '合计';
                $res['合计'][2] = $global_config['VSTR']['ratio']=='default'? $sumVSTR.$global_config['VSTR']['unit']:($global_config['VSTR']['ratio']=='percent'?round( ($sumVSTR/$count), 3).'%':round( ($sumVSTR/$count), 3).':1');
                $res['合计'][3] = $global_config['VSTR']['standard_val'];
                $res['合计'][4] = '合计';

                $res['合计'][5] = $global_config['VETR']['ratio']=='default'? $sumVETR.$global_config['VETR']['unit']:($global_config['VETR']['ratio']=='percent'?round( ($sumVETR/$count), 3).'%':round( ($sumVETR/$count), 3).':1');;
                $res['合计'][6] = $global_config['VETR']['standard_val'];
                $res['合计'][7] = '合计';

                $res['合计'][8] = $global_config['VSMR']['ratio']=='default'? $sumVSMR.$global_config['VSMR']['unit']:($global_config['VSMR']['ratio']=='percent'?round( ($sumVSMR/$count), 3).'%':round( ($sumVSMR/$count), 3).':1');;
                $res['合计'][9] = $global_config['VSMR']['standard_val'];
                $res['合计'][10] = '合计';
                break;    
            case 'specialSchool':
                $index = 1;
                $sumSSTR = 0;
                foreach ($sheetData as $value) {
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='SSTR' && $res[$value->school][2] = $value->found_ind=='SSTR' ? $value->found_val:'';
                    $value->found_ind=='SSTR' && $res[$value->school][3] = $value->found_ind=='SSTR' ? $value->standard_val:'';
                    $value->found_ind=='SSTR' && $res[$value->school][4] = $value->found_ind=='SSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    //---SSTR
                    if ($value->found_ind=='SSTR' && ($global_config['SSTR']['ratio']=='default' || $global_config['SSTR']['ratio']=='percent')) {
                        $sstr = intval($value->found_val);
                    }
                    if ($value->found_ind=='SSTR' && $global_config['SSTR']['ratio']=='scale') {
                        $sstr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='SSTR'&& $value->found_val) && $sumSSTR += $sstr;
                    //-----SSTR
                    $index++;
                }
                if (!$res) {
                    break;
                }
                $count = count($res);
                $res['合计'][0] = '';
                $res['合计'][1] = '合计';
                $res['合计'][2] = $global_config['SSTR']['ratio']=='default'? $sumSSTR.$global_config['SSTR']['unit']:($global_config['SSTR']['ratio']=='percent'?round( ($sumSSTR/$count), 3).'%':round( ($sumSSTR/$count), 3).':1');
                $res['合计'][3] = $global_config['SSTR']['standard_val'];
                $res['合计'][4] = '合计';
                break;
            default:
                return [];
                break;
        }
        if ($res) {
            foreach ($res as $key => &$value) {
                ksort($value);
            }
        }
        Log::info('__getModernQuery');
        Log::info($res);
        return $res;
    }

    private function __getBalanceQuery($school_type, $sheetData)
    {
        $res = [];
        $global_config = config('ixport.SCHOOL_IMPORT_FOUND_INDEX');
        switch ($school_type) {
            case 'primarySchool':
                $index = 1;
                $sumPHETR = 0;
                $sumPHBTR = 0;
                $sumPHATR = 0;
                $sumPSRAR = 0;
                $sumPSMAR = 0;
                $sumPSMR = 0;
                $sumPHIR = 0;
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

                    
                    //---PHETR
                    if ($value->found_ind=='PHETR' && ($global_config['PHETR']['ratio']=='default' || $global_config['PHETR']['ratio']=='percent')) {
                        $phetr = intval($value->found_val);
                    }
                    if ($value->found_ind=='PHETR' && $global_config['PHETR']['ratio']=='scale') {
                        $phetr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='PHETR'&& $value->found_val) && $sumPHETR += $phetr;
                    //-----PHETR
                    //---PHBTR
                    if ($value->found_ind=='PHBTR' && ($global_config['PHBTR']['ratio']=='default' || $global_config['PHBTR']['ratio']=='percent')) {
                        $phbtr = intval($value->found_val);
                    }
                    if ($value->found_ind=='PHBTR' && $global_config['PHBTR']['ratio']=='scale') {
                        $phbtr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='PHBTR'&& $value->found_val) && $sumPHBTR += $phbtr;
                    //-----PHBTR
                    //---PHATR
                    if ($value->found_ind=='PHATR' && ($global_config['PHATR']['ratio']=='default' || $global_config['PHATR']['ratio']=='percent')) {
                        $phatr = intval($value->found_val);
                    }
                    if ($value->found_ind=='PHATR' && $global_config['PHATR']['ratio']=='scale') {
                        $phatr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='PHATR'&& $value->found_val) && $sumPHATR += $phatr;
                    //-----PHATR
                    //---PSRAR
                    if ($value->found_ind=='PSRAR' && ($global_config['PSRAR']['ratio']=='default' || $global_config['PSRAR']['ratio']=='percent')) {
                        $psrar = intval($value->found_val);
                    }
                    if ($value->found_ind=='PSRAR' && $global_config['PSRAR']['ratio']=='scale') {
                        $psrar = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='PSRAR'&& $value->found_val) && $sumPSRAR += $psrar;
                    //-----PSRAR
                    //---PSMAR
                    if ($value->found_ind=='PSMAR' && ($global_config['PSMAR']['ratio']=='default' || $global_config['PSMAR']['ratio']=='percent')) {
                        $psmar = intval($value->found_val);
                    }
                    if ($value->found_ind=='PSMAR' && $global_config['PSMAR']['ratio']=='scale') {
                        $psmar = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='PSMAR'&& $value->found_val) && $sumPSMAR += $psmar;
                    //-----PSMAR
                    //---PSMR
                    if ($value->found_ind=='PSMR' && ($global_config['PSMR']['ratio']=='default' || $global_config['PSMR']['ratio']=='percent')) {
                        $psmr = intval($value->found_val);
                    }
                    if ($value->found_ind=='PSMR' && $global_config['PSMR']['ratio']=='scale') {
                        $psmr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='PSMR'&& $value->found_val) && $sumPSMR += $psmr;
                    //-----PSMR
                    //---PHIR
                    if ($value->found_ind=='PHIR' && ($global_config['PHIR']['ratio']=='default' || $global_config['PHIR']['ratio']=='percent')) {
                        $phir = intval($value->found_val);
                    }
                    if ($value->found_ind=='PHIR' && $global_config['PHIR']['ratio']=='scale') {
                        $phir = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='PHIR'&& $value->found_val) && $sumPHIR += $phir;
                    //-----PHIR
                    
                    
                    $index++;
                }
                if (!$res) {
                    break;
                }
                $count = count($res);
                $res['合计'][0] = '';
                $res['合计'][1] = '合计';

                $res['合计'][2] = $global_config['PHETR']['basic_val'];
                $res['合计'][3] = $global_config['PHETR']['standard_val'];
                $res['合计'][4] = $global_config['PHETR']['ratio']=='default'? $sumPHETR.$global_config['PHETR']['unit']:($global_config['PHETR']['ratio']=='percent'?round( ($sumPHETR/$count), 3).'%':round( ($sumPHETR/$count), 3).':1');
                $res['合计'][5] = '合计';

                $res['合计'][6] = $global_config['PHBTR']['basic_val'];
                $res['合计'][7] = $global_config['PHBTR']['standard_val'];
                $res['合计'][8] = $global_config['PHBTR']['ratio']=='default'? $sumPHBTR.$global_config['PHBTR']['unit']:($global_config['PHBTR']['ratio']=='percent'?round( ($sumPHBTR/$count), 3).'%':round( ($sumPHBTR/$count), 3).':1');
                $res['合计'][9] = '合计';

                $res['合计'][10] = $global_config['PHATR']['basic_val'];
                $res['合计'][11] = $global_config['PHATR']['standard_val'];
                $res['合计'][12] = $global_config['PHATR']['ratio']=='default'? $sumPHATR.$global_config['PHATR']['unit']:($global_config['PHATR']['ratio']=='percent'?round( ($sumPHATR/$count), 3).'%':round( ($sumPHATR/$count), 3).':1');
                $res['合计'][13] = '合计';

                $res['合计'][14] = $global_config['PSRAR']['basic_val'];
                $res['合计'][15] = $global_config['PSRAR']['standard_val'];
                $res['合计'][16] = $global_config['PSRAR']['ratio']=='default'? $sumPSRAR.$global_config['PSRAR']['unit']:($global_config['PSRAR']['ratio']=='percent'?round( ($sumPSRAR/$count), 3).'%':round( ($sumPSRAR/$count), 3).':1');
                $res['合计'][17] = '合计';

                $res['合计'][18] = $global_config['PSMAR']['basic_val'];
                $res['合计'][19] = $global_config['PSMAR']['standard_val'];
                $res['合计'][20] = $global_config['PSMAR']['ratio']=='default'? $sumPSMAR.$global_config['PSMAR']['unit']:($global_config['PSMAR']['ratio']=='percent'?round( ($sumPSMAR/$count), 3).'%':round( ($sumPSMAR/$count), 3).':1');
                $res['合计'][21] = '合计';

                $res['合计'][22] = $global_config['PSMR']['basic_val'];
                $res['合计'][23] = $global_config['PSMR']['standard_val'];
                $res['合计'][24] = $global_config['PSMR']['ratio']=='default'? $sumPSMR.$global_config['PSMR']['unit']:($global_config['PSMR']['ratio']=='percent'?round( ($sumPSMR/$count), 3).'%':round( ($sumPSMR/$count), 3).':1');
                $res['合计'][25] = '合计';

                $res['合计'][26] = $global_config['PHIR']['basic_val'];
                $res['合计'][27] = $global_config['PHIR']['standard_val'];
                $res['合计'][28] = $global_config['PHIR']['ratio']=='default'? $sumPHIR.$global_config['PHIR']['unit']:($global_config['PHIR']['ratio']=='percent'?round( ($sumPHIR/$count), 3).'%':round( ($sumPHIR/$count), 3).':1');
                $res['合计'][29] = '合计';


                break;
            case 'juniorMiddleSchool':
                $index = 1;
                $sumJHETR = 0;
                $sumJHBTR = 0;
                $sumJHATR = 0;
                $sumJSRAR = 0;
                $sumJSMAR = 0;
                $sumJSMR = 0;
                $sumJHIR = 0;
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

                    
                    //---JHETR
                    if ($value->found_ind=='JHETR' && ($global_config['JHETR']['ratio']=='default' || $global_config['JHETR']['ratio']=='percent')) {
                        $jhetr = intval($value->found_val);
                    }
                    if ($value->found_ind=='JHETR' && $global_config['JHETR']['ratio']=='scale') {
                        $jhetr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='JHETR'&& $value->found_val) && $sumJHETR += $jhetr;
                    //-----JHETR
                    //---JHBTR
                    if ($value->found_ind=='JHBTR' && ($global_config['JHBTR']['ratio']=='default' || $global_config['JHBTR']['ratio']=='percent')) {
                        $jhbtr = intval($value->found_val);
                    }
                    if ($value->found_ind=='JHBTR' && $global_config['JHBTR']['ratio']=='scale') {
                        $jhbtr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='JHBTR'&& $value->found_val) && $sumJHBTR += $jhbtr;
                    //-----JHBTR
                    //---JHATR
                    if ($value->found_ind=='JHATR' && ($global_config['JHATR']['ratio']=='default' || $global_config['JHATR']['ratio']=='percent')) {
                        $jhatr = intval($value->found_val);
                    }
                    if ($value->found_ind=='JHATR' && $global_config['JHATR']['ratio']=='scale') {
                        $jhatr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='JHATR'&& $value->found_val) && $sumJHATR+= $jhatr;
                    //-----JHATR
                    //---JSRAR
                    if ($value->found_ind=='JSRAR' && ($global_config['JSRAR']['ratio']=='default' || $global_config['JSRAR']['ratio']=='percent')) {
                        $jsrar = intval($value->found_val);
                    }
                    if ($value->found_ind=='JSRAR' && $global_config['JSRAR']['ratio']=='scale') {
                        $jsrar = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='JSRAR'&& $value->found_val) && $sumJSRAR += $jsrar;
                    //-----JSRAR
                    //---JSMAR
                    if ($value->found_ind=='JSMAR' && ($global_config['JSMAR']['ratio']=='default' || $global_config['JSMAR']['ratio']=='percent')) {
                        $jsmar = intval($value->found_val);
                    }
                    if ($value->found_ind=='JSMAR' && $global_config['JSMAR']['ratio']=='scale') {
                        $jsmar = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='JSMAR'&& $value->found_val) && $sumJSMAR += $jsmar;
                    //-----JSMAR
                    //---JSMR
                    if ($value->found_ind=='JSMR' && ($global_config['JSMR']['ratio']=='default' || $global_config['JSMR']['ratio']=='percent')) {
                        $jsmr = intval($value->found_val);
                    }
                    if ($value->found_ind=='JSMR' && $global_config['JSMR']['ratio']=='scale') {
                        $jsmr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='JSMR'&& $value->found_val) && $sumJSMR += $jsmr;
                    //-----JSMR
                    //---JHIR
                    if ($value->found_ind=='JHIR' && ($global_config['JHIR']['ratio']=='default' || $global_config['JHIR']['ratio']=='percent')) {
                        $jhir = intval($value->found_val);
                    }
                    if ($value->found_ind=='JHIR' && $global_config['JHIR']['ratio']=='scale') {
                        $jhir = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='JHIR'&& $value->found_val) && $sumJHIR += $jhir;
                    //-----JHIR
                    $index++;
                }

                if (!$res) {
                    break;
                }
                $count = count($res);
                $res['合计'][0] = '';
                $res['合计'][1] = '合计';

                $res['合计'][2] = $global_config['JHETR']['basic_val'];
                $res['合计'][3] = $global_config['JHETR']['standard_val'];
                $res['合计'][4] = $global_config['JHETR']['ratio']=='default'? $sumJHETR.$global_config['JHETR']['unit']:($global_config['JHETR']['ratio']=='percent'?round( ($sumJHETR/$count), 3).'%':round( ($sumJHETR/$count), 3).':1');
                $res['合计'][5] = '合计';

                $res['合计'][6] = $global_config['JHBTR']['basic_val'];
                $res['合计'][7] = $global_config['JHBTR']['standard_val'];
                $res['合计'][8] = $global_config['JHBTR']['ratio']=='default'? $sumJHBTR.$global_config['JHBTR']['unit']:($global_config['JHBTR']['ratio']=='percent'?round( ($sumJHBTR/$count), 3).'%':round( ($sumJHBTR/$count), 3).':1');
                $res['合计'][9] = '合计';

                $res['合计'][10] = $global_config['JHATR']['basic_val'];
                $res['合计'][11] = $global_config['JHATR']['standard_val'];
                $res['合计'][12] = $global_config['JHATR']['ratio']=='default'? $sumJHATR.$global_config['JHATR']['unit']:($global_config['JHATR']['ratio']=='percent'?round( ($sumJHATR/$count), 3).'%':round( ($sumJHATR/$count), 3).':1');
                $res['合计'][13] = '合计';

                $res['合计'][14] = $global_config['JSRAR']['basic_val'];
                $res['合计'][15] = $global_config['JSRAR']['standard_val'];
                $res['合计'][16] = $global_config['JSRAR']['ratio']=='default'? $sumJSRAR.$global_config['JSRAR']['unit']:($global_config['JSRAR']['ratio']=='percent'?round( ($sumJSRAR/$count), 3).'%':round( ($sumJSRAR/$count), 3).':1');
                $res['合计'][17] = '合计';

                $res['合计'][18] = $global_config['JSMAR']['basic_val'];
                $res['合计'][19] = $global_config['JSMAR']['standard_val'];
                $res['合计'][20] = $global_config['JSMAR']['ratio']=='default'? $sumJSMAR.$global_config['JSMAR']['unit']:($global_config['JSMAR']['ratio']=='percent'?round( ($sumJSMAR/$count), 3).'%':round( ($sumJSMAR/$count), 3).':1');
                $res['合计'][21] = '合计';

                $res['合计'][22] = $global_config['JSMR']['basic_val'];
                $res['合计'][23] = $global_config['JSMR']['standard_val'];
                $res['合计'][24] = $global_config['JSMR']['ratio']=='default'? $sumJSMR.$global_config['JSMR']['unit']:($global_config['JSMR']['ratio']=='percent'?round( ($sumJSMR/$count), 3).'%':round( ($sumJSMR/$count), 3).':1');
                $res['合计'][25] = '合计';

                $res['合计'][26] = $global_config['JHIR']['basic_val'];
                $res['合计'][27] = $global_config['JHIR']['standard_val'];
                $res['合计'][28] = $global_config['JHIR']['ratio']=='default'? $sumJHIR.$global_config['JHIR']['unit']:($global_config['JHIR']['ratio']=='percent'?round( ($sumJHIR/$count), 3).'%':round( ($sumJHIR/$count), 3).':1');
                $res['合计'][29] = '合计';
                break;
            case 'nineYearCon':
                $index = 1;
                $sumNHETR = 0;
                $sumNHBTR = 0;
                $sumNHATR = 0;
                $sumNSRAR = 0;
                $sumNSMAR = 0;
                $sumNSMR = 0;
                $sumNHIR = 0;
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

                    $value->found_ind=='NHATR' && $res[$value->school][10] = $value->found_ind=='NHATR' ? $value->basic_val:'';
                    $value->found_ind=='NHATR' && $res[$value->school][11] = $value->found_ind=='NHATR' ? $value->standard_val:'';
                    $value->found_ind=='NHATR' && $res[$value->school][12] = $value->found_ind=='NHATR' ? $value->found_val:'';
                    $value->found_ind=='NHATR' && $res[$value->school][13] = $value->found_ind=='NHATR' ? ($value->is_standard == 1?'达标':'不达标'):'';

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

                    
                    //---NHETR
                    if ($value->found_ind=='NHETR' && ($global_config['NHETR']['ratio']=='default' || $global_config['NHETR']['ratio']=='percent')) {
                        $nhetr = intval($value->found_val);
                    }
                    if ($value->found_ind=='NHETR' && $global_config['NHETR']['ratio']=='scale') {
                        $nhetr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='NHETR'&& $value->found_val) && $sumNHETR += $nhetr;
                    //-----NHETR
                    //---NHBTR
                    if ($value->found_ind=='NHBTR' && ($global_config['NHBTR']['ratio']=='default' || $global_config['NHBTR']['ratio']=='percent')) {
                        $nhbtr = intval($value->found_val);
                    }
                    if ($value->found_ind=='NHBTR' && $global_config['NHBTR']['ratio']=='scale') {
                        $nhbtr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='NHBTR'&& $value->found_val) && $sumNHBTR += $nhbtr;
                    //-----NHBTR
                    //---NHATR
                    if ($value->found_ind=='NHATR' && ($global_config['NHATR']['ratio']=='default' || $global_config['NHATR']['ratio']=='percent')) {
                        $nhatr = intval($value->found_val);
                    }
                    if ($value->found_ind=='NHATR' && $global_config['NHATR']['ratio']=='scale') {
                        $nhatr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='NHATR'&& $value->found_val) && $sumNHATR+= $nhatr;
                    //-----NHATR
                    //---NSRAR
                    if ($value->found_ind=='NSRAR' && ($global_config['NSRAR']['ratio']=='default' || $global_config['NSRAR']['ratio']=='percent')) {
                        $nsrar = intval($value->found_val);
                    }
                    if ($value->found_ind=='NSRAR' && $global_config['NSRAR']['ratio']=='scale') {
                        $nsrar = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='NSRAR'&& $value->found_val) && $sumNSRAR += $nsrar;
                    //-----NSRAR
                    //---NSMAR
                    if ($value->found_ind=='NSMAR' && ($global_config['NSMAR']['ratio']=='default' || $global_config['NSMAR']['ratio']=='percent')) {
                        $nsmar = intval($value->found_val);
                    }
                    if ($value->found_ind=='NSMAR' && $global_config['NSMAR']['ratio']=='scale') {
                        $nsmar = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='NSMAR'&& $value->found_val) && $sumNSMAR += $nsmar;
                    //-----NSMAR
                    //---NSMR
                    if ($value->found_ind=='NSMR' && ($global_config['NSMR']['ratio']=='default' || $global_config['NSMR']['ratio']=='percent')) {
                        $nsmr = intval($value->found_val);
                    }
                    if ($value->found_ind=='NSMR' && $global_config['NSMR']['ratio']=='scale') {
                        $nsmr = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='NSMR'&& $value->found_val) && $sumNSMR += $nsmr;
                    //-----NSMR
                    //---NHIR
                    if ($value->found_ind=='NHIR' && ($global_config['NHIR']['ratio']=='default' || $global_config['NHIR']['ratio']=='percent')) {
                        $nhir = intval($value->found_val);
                    }
                    if ($value->found_ind=='NHIR' && $global_config['NHIR']['ratio']=='scale') {
                        $nhir = explode(':', $value->found_val)[0];
                    }
                    ($value->found_ind=='NHIR'&& $value->found_val) && $sumNHIR += $nhir;
                    //-----NHIR
                    $index++;
                }
                if (!$res) {
                    break;
                }
                $count = count($res);
                $res['合计'][0] = '';
                $res['合计'][1] = '合计';

                $res['合计'][2] = $global_config['NHETR']['basic_val'];
                $res['合计'][3] = $global_config['NHETR']['standard_val'];
                $res['合计'][4] = $global_config['NHETR']['ratio']=='default'? $sumNHETR.$global_config['NHETR']['unit']:($global_config['NHETR']['ratio']=='percent'?round( ($sumNHETR/$count), 3).'%':round( ($sumNHETR/$count), 3).':1');
                $res['合计'][5] = '合计';

                $res['合计'][6] = $global_config['NHBTR']['basic_val'];
                $res['合计'][7] = $global_config['NHBTR']['standard_val'];
                $res['合计'][8] = $global_config['NHBTR']['ratio']=='default'? $sumNHBTR.$global_config['NHBTR']['unit']:($global_config['NHBTR']['ratio']=='percent'?round( ($sumNHBTR/$count), 3).'%':round( ($sumNHBTR/$count), 3).':1');
                $res['合计'][9] = '合计';

                $res['合计'][10] = $global_config['NHATR']['basic_val'];
                $res['合计'][11] = $global_config['NHATR']['standard_val'];
                $res['合计'][12] = $global_config['NHATR']['ratio']=='default'? $sumNHATR.$global_config['NHATR']['unit']:($global_config['NHATR']['ratio']=='percent'?round( ($sumNHATR/$count), 3).'%':round( ($sumNHATR/$count), 3).':1');
                $res['合计'][13] = '合计';

                $res['合计'][14] = $global_config['NSRAR']['basic_val'];
                $res['合计'][15] = $global_config['NSRAR']['standard_val'];
                $res['合计'][16] = $global_config['NSRAR']['ratio']=='default'? $sumNSRAR.$global_config['NSRAR']['unit']:($global_config['NSRAR']['ratio']=='percent'?round( ($sumNSRAR/$count), 3).'%':round( ($sumNSRAR/$count), 3).':1');
                $res['合计'][17] = '合计';

                $res['合计'][18] = $global_config['NSMAR']['basic_val'];
                $res['合计'][19] = $global_config['NSMAR']['standard_val'];
                $res['合计'][20] = $global_config['NSMAR']['ratio']=='default'? $sumNSMAR.$global_config['NSMAR']['unit']:($global_config['NSMAR']['ratio']=='percent'?round( ($sumNSMAR/$count), 3).'%':round( ($sumNSMAR/$count), 3).':1');
                $res['合计'][21] = '合计';

                $res['合计'][22] = $global_config['NSMR']['basic_val'];
                $res['合计'][23] = $global_config['NSMR']['standard_val'];
                $res['合计'][24] = $global_config['NSMR']['ratio']=='default'? $sumNSMR.$global_config['NSMR']['unit']:($global_config['NSMR']['ratio']=='percent'?round( ($sumNSMR/$count), 3).'%':round( ($sumNSMR/$count), 3).':1');
                $res['合计'][25] = '合计';

                $res['合计'][26] = $global_config['NHIR']['basic_val'];
                $res['合计'][27] = $global_config['NHIR']['standard_val'];
                $res['合计'][28] = $global_config['NHIR']['ratio']=='default'? $sumNHIR.$global_config['NHIR']['unit']:($global_config['NHIR']['ratio']=='percent'?round( ($sumNHIR/$count), 3).'%':round( ($sumNHIR/$count), 3).':1');
                $res['合计'][29] = '合计';
                break;
            default:
                return [];
                break;
        }

        if ($res) {
            foreach ($res as $key => &$value) {
                ksort($value);
            }
        }

        return $res;
    }

}
