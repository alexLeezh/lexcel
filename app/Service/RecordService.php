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
            case 'situation':
                //特殊处理 【全国义务教育优质均衡县校际均衡情况】
                $sheetData = app('db')->select("SELECT * FROM sheet_record where report_type = 'balance'  and user_id = '".$user_id." ' ");
                $res = $this->__getSituationQuery($sheetData);
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
        $repeat = [];
        $global_config = config('ixport.SCHOOL_IMPORT_FOUND_INDEX');
        switch ($school_type) {
            case 'kindergarten':
                $index = 1;
                $sumKCTR = 0;
                $sumKSTR = 0;
                $kctr = 0;
                $kstr = 0;
                $res = [];
                
                foreach ($sheetData as $value) {
                    if (in_array($value->user_id.$value->school.$value->school_type.$value->found_ind, $repeat)) {
                        continue;
                    }
                    //并发重复数据处理
                    $repeat[] = $value->user_id.$value->school.$value->school_type.$value->found_ind;

                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='KCTR'&& $res[$value->school][2] = $value->found_ind=='KCTR' ? $value->found_val : '';
                    $value->found_ind=='KCTR'&& $res[$value->school][3] = $value->found_ind=='KCTR' ? $value->standard_val : '';

                    $value->found_ind=='KCTR'&& $res[$value->school][4] = $value->found_ind=='KCTR' ? $value->found_divisor : '';
                    $value->found_ind=='KCTR'&& $res[$value->school][5] = $value->found_ind=='KCTR' ? $value->found_divider : '';

                    $value->found_ind=='KCTR'&& $res[$value->school][6] = $value->found_ind=='KCTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    $value->found_ind=='KSTR'&& $res[$value->school][7] = $value->found_ind=='KSTR' ? $value->found_val:'';
                    $value->found_ind=='KSTR'&& $res[$value->school][8] = $value->found_ind=='KSTR' ? $value->standard_val:'';

                    $value->found_ind=='KSTR'&& $res[$value->school][9] = $value->found_ind=='KSTR' ? $value->found_divisor : '';
                    $value->found_ind=='KSTR'&& $res[$value->school][10] = $value->found_ind=='KSTR' ? $value->found_divider : '';

                    $value->found_ind=='KSTR'&& $res[$value->school][11] = $value->found_ind=='KSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    //KCTR
                    ($value->found_ind=='KCTR'&& $value->found_divisor) && $sumKCTR += floatval($value->found_divisor);
                    ($value->found_ind=='KCTR'&& $value->found_divider) && $kctr += floatval($value->found_divider);
                    // if ($value->found_ind=='KCTR' && ($global_config['KCTR']['ratio']=='default' || $global_config['KCTR']['ratio']=='percent')) {
                    //     $kctr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='KCTR' && $global_config['KCTR']['ratio']=='scale') {
                    //     $kctr = explode(':', $value->found_val)[0];
                    // }
                    //KCTR
                    
                    //KSTR
                    // if ($value->found_ind=='KSTR' && ($global_config['KSTR']['ratio']=='default' || $global_config['KSTR']['ratio']=='percent')) {
                    //     $kstr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='KSTR' && $global_config['KSTR']['ratio']=='scale') {
                    //     $kstr = explode(':', $value->found_val)[0];
                    // }
                    ($value->found_ind=='KSTR'&& $value->found_divisor) && $sumKSTR += floatval($value->found_divisor);
                    ($value->found_ind=='KSTR'&& $value->found_divider) && $kstr += floatval($value->found_divider);
                    //KSTR
                    
                    // ($value->found_ind=='KCTR'&& $value->found_val) && $sumKCTR += $kctr;
                    // ($value->found_ind=='KSTR'&& $value->found_val) && $sumKSTR += $kstr;
                    $index++;
                }
                if (!$res) {
                    break;
                }
                $count = count($res);
                //合计
                $res['合计'][0] = '';
                $res['合计'][1] = '合计';
                // $res['合计'][2] = $global_config['KCTR']['ratio']=='default'? round( ($sumKCTR/$count), 3).$global_config['KCTR']['unit']:($global_config['KCTR']['ratio']=='percent'?round( ($sumKCTR/$count), 3).'%':round( ($sumKCTR/$count), 3).':1');
                $res['合计'][2] = ($kctr == 0 || $sumKCTR==0  ) ? '0' : ($global_config['KCTR']['ratio']=='default'? round( ($sumKCTR/$kctr), 3).$global_config['KCTR']['unit']:($global_config['KCTR']['ratio']=='percent'?round( ($sumKCTR/$kctr)*100, 3).'%':round( ($sumKCTR/$kctr), 3).':1') );
                $res['合计'][3] = $global_config['KCTR']['standard_val'];
                $res['合计'][4] = $sumKCTR;
                $res['合计'][5] = $kctr;
                $res['合计'][6] = '合计';

                // $res['合计'][7] = $global_config['KSTR']['ratio']=='default'? round( ($sumKSTR/$count), 3).$global_config['KSTR']['unit']:($global_config['KSTR']['ratio']=='percent'?round( ($sumKSTR/$count), 3).'%':round( ($sumKSTR/$count), 3).':1');

                $res['合计'][7] = ($kstr == 0 || $sumKSTR==0  ) ? '0' : ($global_config['KSTR']['ratio']=='default'? round( ($sumKSTR/$kstr), 3).$global_config['KSTR']['unit']:($global_config['KSTR']['ratio']=='percent'?round( ($sumKSTR/$kstr)*100, 3).'%':round( ($sumKSTR/$kstr), 3).':1') ); 
                $res['合计'][8] = $global_config['KSTR']['standard_val'];
                $res['合计'][9] = $sumKSTR;
                $res['合计'][10] = $kstr;
                $res['合计'][11] = '合计';

                break;
            case 'primarySchool':
                $res = [];
                $index = 1;
                $sumPSTR = 0;
                $sumPTR = 0;
                $sumPHSTR = 0;
                $sumPFCR = 0;
                $sumPSBR = 0;
                $pstr = 0;
                $ptr = 0;
                $pfcr = 0;
                $phstr = 0;
                $psbr = 0;
                foreach ($sheetData as $value) {
                    if (in_array($value->user_id.$value->school.$value->school_type.$value->found_ind, $repeat)) {
                        continue;
                    }
                    //并发重复数据处理
                    $repeat[] = $value->user_id.$value->school.$value->school_type.$value->found_ind;
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='PSTR' && $res[$value->school][2] = $value->found_ind=='PSTR' ? $value->found_val:'';
                    $value->found_ind=='PSTR' && $res[$value->school][3] = $value->found_ind=='PSTR' ? $value->standard_val:'';
                    $value->found_ind=='PSTR' && $res[$value->school][4] = $value->found_ind=='PSTR' ? $value->found_divisor:'';
                    $value->found_ind=='PSTR' && $res[$value->school][5] = $value->found_ind=='PSTR' ? $value->found_divider:'';
                    $value->found_ind=='PSTR' && $res[$value->school][6] = $value->found_ind=='PSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PTR' && $res[$value->school][7] = $value->found_ind=='PTR' ? $value->found_val:'';
                    $value->found_ind=='PTR' && $res[$value->school][8] = $value->found_ind=='PTR' ? $value->standard_val:'';
                    $value->found_ind=='PTR' && $res[$value->school][9] = $value->found_ind=='PTR' ? $value->found_divisor:'';
                    $value->found_ind=='PTR' && $res[$value->school][10] = $value->found_ind=='PTR' ? $value->found_divider:'';
                    $value->found_ind=='PTR' && $res[$value->school][11] = $value->found_ind=='PTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PFCR' && $res[$value->school][12] = $value->found_ind=='PFCR' ? $value->found_val:'';
                    $value->found_ind=='PFCR' && $res[$value->school][13] = $value->found_ind=='PFCR' ? $value->standard_val:'';
                    $value->found_ind=='PFCR' && $res[$value->school][14] = $value->found_ind=='PFCR' ? $value->found_divisor:'';
                    $value->found_ind=='PFCR' && $res[$value->school][15] = $value->found_ind=='PFCR' ? $value->found_divider:'';
                    $value->found_ind=='PFCR' && $res[$value->school][16] = $value->found_ind=='PFCR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PHSTR' && $res[$value->school][17] = $value->found_ind=='PHSTR' ? $value->found_val:'';
                    $value->found_ind=='PHSTR' && $res[$value->school][18] = $value->found_ind=='PHSTR' ? $value->standard_val:'';
                    $value->found_ind=='PHSTR' && $res[$value->school][19] = $value->found_ind=='PHSTR' ? $value->found_divisor:'';
                    $value->found_ind=='PHSTR' && $res[$value->school][20] = $value->found_ind=='PHSTR' ? $value->found_divider:'';
                    $value->found_ind=='PHSTR' && $res[$value->school][21] = $value->found_ind=='PHSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PSBR' && $res[$value->school][22] = $value->found_ind=='PSBR' ? $value->found_val:'';
                    $value->found_ind=='PSBR' && $res[$value->school][23] = $value->found_ind=='PSBR' ? $value->standard_val:'';
                    $value->found_ind=='PSBR' && $res[$value->school][24] = $value->found_ind=='PSBR' ? $value->found_divisor:'';
                    $value->found_ind=='PSBR' && $res[$value->school][25] = $value->found_ind=='PSBR' ? $value->found_divider:'';
                    $value->found_ind=='PSBR' && $res[$value->school][26] = $value->found_ind=='PSBR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    //------PSTR
                    // if ($value->found_ind=='PSTR' && ($global_config['PSTR']['ratio']=='default' || $global_config['PSTR']['ratio']=='percent')) {
                    //     $pstr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='PSTR' && $global_config['PSTR']['ratio']=='scale') {
                    //     $pstr = explode(':', $value->found_val)[0];
                    // }

                    ($value->found_ind=='PSTR'&& $value->found_divisor) && $sumPSTR += floatval($value->found_divisor);
                    ($value->found_ind=='PSTR'&& $value->found_divider) && $pstr += floatval($value->found_divider);
                    //---PSTR
                    //
                    //PTR
                    // if ($value->found_ind=='PTR' && ($global_config['PTR']['ratio']=='default' || $global_config['PTR']['ratio']=='percent')) {
                    //     $ptr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='PTR' && $global_config['PTR']['ratio']=='scale') {
                    //     $ptr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='PTR'&& $value->found_val) && $sumPTR += $ptr;
                    ($value->found_ind=='PTR'&& $value->found_divisor) && $sumPTR += floatval($value->found_divisor);
                    ($value->found_ind=='PTR'&& $value->found_divider) && $ptr += floatval($value->found_divider);
                    //---PTR
                    
                    //---PFCR
                    // if ($value->found_ind=='PFCR' && ($global_config['PFCR']['ratio']=='default' || $global_config['PFCR']['ratio']=='percent')) {
                    //     $pfcr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='PFCR' && $global_config['PFCR']['ratio']=='scale') {
                    //     $pfcr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='PFCR'&& $value->found_val) && $sumPFCR += $pfcr;
                    ($value->found_ind=='PFCR'&& $value->found_divisor) && $sumPFCR += floatval($value->found_divisor);
                    ($value->found_ind=='PFCR'&& $value->found_divider) && $pfcr += floatval($value->found_divider);
                    //-----PFCR

                    //-------PHSTR
                    // if ($value->found_ind=='PHSTR' && ($global_config['PHSTR']['ratio']=='default' || $global_config['PHSTR']['ratio']=='percent')) {
                    //     $phstr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='PHSTR' && $global_config['PHSTR']['ratio']=='scale') {
                    //     $phstr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='PHSTR'&& $value->found_val) && $sumPHSTR += $phstr;
                    ($value->found_ind=='PHSTR'&& $value->found_divisor) && $sumPHSTR += floatval($value->found_divisor);
                    ($value->found_ind=='PHSTR'&& $value->found_divider) && $phstr += floatval($value->found_divider);
                    //---PHSTR
                    
                    //-------PSBR
                    // if ($value->found_ind=='PSBR' && ($global_config['PSBR']['ratio']=='default' || $global_config['PSBR']['ratio']=='percent')) {
                    //     $psbr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='PSBR' && $global_config['PSBR']['ratio']=='scale') {
                    //     $psbr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='PSBR'&& $value->found_val) && $sumPSBR += $psbr;
                    ($value->found_ind=='PSBR'&& $value->found_divisor) && $sumPSBR += floatval($value->found_divisor);
                    ($value->found_ind=='PSBR'&& $value->found_divider) && $psbr += floatval($value->found_divider);
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
                // $res['合计'][2] = $global_config['PSTR']['ratio']=='default'? round( ($sumPSTR/$count), 3).$global_config['PSTR']['unit']:($global_config['PSTR']['ratio']=='percent'?round( ($sumPSTR/$count), 3).'%':round( ($sumPSTR/$count), 3).':1');
                $res['合计'][2] = ($pstr == 0 || $sumPSTR==0  ) ? '0' :  ($global_config['PSTR']['ratio']=='default'? round( ($sumPSTR/$pstr), 3).$global_config['PSTR']['unit']:($global_config['PSTR']['ratio']=='percent'?round( ($sumPSTR/$pstr)*100, 3).'%':round( ($sumPSTR/$pstr), 3).':1') );
                $res['合计'][3] = $global_config['PSTR']['standard_val'];
                $res['合计'][4] = $sumPSTR;
                $res['合计'][5] = $pstr;
                $res['合计'][6] = '合计';

                // $res['合计'][7] = $global_config['PTR']['ratio']=='default'? round( ($sumPTR/$count), 3).$global_config['PTR']['unit']:($global_config['PTR']['ratio']=='percent'?round( ($sumPTR/$count), 3).'%':round( ($sumPTR/$count), 3).':1');;
                $res['合计'][7] = ($ptr == 0 || $sumPTR==0  ) ? '0' :  ($global_config['PTR']['ratio']=='default'? round( ($sumPTR/$ptr), 3).$global_config['PTR']['unit']:($global_config['PTR']['ratio']=='percent'?round( ($sumPTR/$ptr)*100, 3).'%':round( ($sumPTR/$ptr), 3).':1') );
                $res['合计'][8] = $global_config['PTR']['standard_val'];
                $res['合计'][8] = $global_config['PTR']['standard_val'];
                $res['合计'][9] = $sumPTR;
                $res['合计'][10] = $ptr;
                $res['合计'][11] = '合计';

                // $res['合计'][12] = $global_config['PFCR']['ratio']=='default'? round( ($sumPFCR/$count), 3).$global_config['PFCR']['unit']:($global_config['PFCR']['ratio']=='percent'?round( ($sumPFCR/$count), 3).'%':round( ($sumPFCR/$count), 3).':1');
                $res['合计'][12] =  ($pfcr == 0 || $sumPFCR==0  ) ? '0' :  ($global_config['PFCR']['ratio']=='default'? round( ($sumPFCR/$pfcr), 3).$global_config['PFCR']['unit']:($global_config['PFCR']['ratio']=='percent'?round( ($sumPFCR/$pfcr)*100, 3).'%':round( ($sumPFCR/$pfcr), 3).':1') );
                $res['合计'][13] = $global_config['PFCR']['standard_val'];
                $res['合计'][14] = $sumPFCR;
                $res['合计'][15] = $pfcr;
                $res['合计'][16] = '合计';

                // $res['合计'][17] = $global_config['PHSTR']['ratio']=='default'? round( ($sumPHSTR/$count), 3).$global_config['PHSTR']['unit']:($global_config['PHSTR']['ratio']=='percent'?round( ($sumPHSTR/$count), 3).'%':round( ($sumPHSTR/$count), 3).':1');
                $res['合计'][17] = ($phstr == 0 || $sumPHSTR==0  ) ? '0' :  ($global_config['PHSTR']['ratio']=='default'? round( ($sumPHSTR/$phstr), 3).$global_config['PHSTR']['unit']:($global_config['PHSTR']['ratio']=='percent'?round( ($sumPHSTR/$phstr)*100, 3).'%':round( ($sumPHSTR/$phstr), 3).':1') ); 
                $res['合计'][18] = $global_config['PHSTR']['standard_val'];
                $res['合计'][19] = $sumPHSTR;
                $res['合计'][20] = $phstr;
                $res['合计'][21] = '合计';

                // $res['合计'][22] = $global_config['PSBR']['ratio']=='default'? round( ($sumPSBR/$count), 3).$global_config['PSBR']['unit']:($global_config['PSBR']['ratio']=='percent'?round( ($sumPSBR/$count), 3).'%':round( ($sumPSBR/$count), 3).':1');
                $res['合计'][22] = ($psbr == 0 || $sumPSBR==0  ) ? '0' :  ($global_config['PSBR']['ratio']=='default'? round( ($sumPSBR/$psbr), 3).$global_config['PSBR']['unit']:($global_config['PSBR']['ratio']=='percent'?round( ($sumPSBR/$psbr)*100, 3).'%':round( ($sumPSBR/$psbr), 3).':1') );
                $res['合计'][23] = $global_config['PSBR']['standard_val'];
                $res['合计'][24] = $sumPSBR;
                $res['合计'][25] = $psbr;
                $res['合计'][26] = '合计';
                
                break;
            case 'juniorMiddleSchool':
                $res = [];
                $index = 1;
                $sumJSTR = 0;
                $sumJETR = 0;
                $sumJFCR = 0;
                $sumJHSTR = 0;
                $sumJSBR = 0;
                $jstr = 0;
                $jetr = 0;
                $jfcr = 0;
                $jhstr = 0;
                $jsbr = 0;
                foreach ($sheetData as $value) {
                    if (in_array($value->user_id.$value->school.$value->school_type.$value->found_ind, $repeat)) {
                        continue;
                    }
                    //并发重复数据处理
                    $repeat[] = $value->user_id.$value->school.$value->school_type.$value->found_ind;
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='JSTR' && $res[$value->school][2] = $value->found_ind=='JSTR' ? $value->found_val:'';
                    $value->found_ind=='JSTR' && $res[$value->school][3] = $value->found_ind=='JSTR' ? $value->standard_val:'';
                    $value->found_ind=='JSTR' && $res[$value->school][4] = $value->found_ind=='JSTR' ? $value->found_divisor:'';
                    $value->found_ind=='JSTR' && $res[$value->school][5] = $value->found_ind=='JSTR' ? $value->found_divider:'';
                    $value->found_ind=='JSTR' && $res[$value->school][6] = $value->found_ind=='JSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JETR' && $res[$value->school][7] = $value->found_ind=='JETR' ? $value->found_val:'';
                    $value->found_ind=='JETR' && $res[$value->school][8] = $value->found_ind=='JETR' ? $value->standard_val:'';
                    $value->found_ind=='JETR' && $res[$value->school][9] = $value->found_ind=='JETR' ? $value->found_divisor:'';
                    $value->found_ind=='JETR' && $res[$value->school][10] = $value->found_ind=='JETR' ? $value->found_divider:'';
                    $value->found_ind=='JETR' && $res[$value->school][11] = $value->found_ind=='JETR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JFCR' && $res[$value->school][12] = $value->found_ind=='JFCR' ? $value->found_val:'';
                    $value->found_ind=='JFCR' && $res[$value->school][13] = $value->found_ind=='JFCR' ? $value->standard_val:'';
                    $value->found_ind=='JFCR' && $res[$value->school][14] = $value->found_ind=='JFCR' ? $value->found_divisor:'';
                    $value->found_ind=='JFCR' && $res[$value->school][15] = $value->found_ind=='JFCR' ? $value->found_divider:'';
                    $value->found_ind=='JFCR' && $res[$value->school][16] = $value->found_ind=='JFCR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JHSTR' && $res[$value->school][17] = $value->found_ind=='JHSTR' ? $value->found_val:'';
                    $value->found_ind=='JHSTR' && $res[$value->school][18] = $value->found_ind=='JHSTR' ? $value->standard_val:'';
                    $value->found_ind=='JHSTR' && $res[$value->school][19] = $value->found_ind=='JHSTR' ? $value->found_divisor:'';
                    $value->found_ind=='JHSTR' && $res[$value->school][20] = $value->found_ind=='JHSTR' ? $value->found_divider:'';
                    $value->found_ind=='JHSTR' && $res[$value->school][21] = $value->found_ind=='JHSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JSBR' && $res[$value->school][22] = $value->found_ind=='JSBR' ? $value->found_val:'';
                    $value->found_ind=='JSBR' && $res[$value->school][23] = $value->found_ind=='JSBR' ? $value->standard_val:'';
                    $value->found_ind=='JSBR' && $res[$value->school][24] = $value->found_ind=='JSBR' ? $value->found_divisor:'';
                    $value->found_ind=='JSBR' && $res[$value->school][25] = $value->found_ind=='JSBR' ? $value->found_divider:'';
                    $value->found_ind=='JSBR' && $res[$value->school][26] = $value->found_ind=='JSBR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    //------JSTR
                    // if ($value->found_ind=='JSTR' && ($global_config['JSTR']['ratio']=='default' || $global_config['JSTR']['ratio']=='percent')) {
                    //     $jstr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='JSTR' && $global_config['JSTR']['ratio']=='scale') {
                    //     $jstr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='JSTR'&& $value->found_val) && $sumJSTR += $jstr;
                    ($value->found_ind=='JSTR'&& $value->found_divisor) && $sumJSTR += floatval($value->found_divisor);
                    ($value->found_ind=='JSTR'&& $value->found_divider) && $jstr += floatval($value->found_divider);
                    //---JSTR
                    //
                    //JETR
                    // if ($value->found_ind=='JETR' && ($global_config['JETR']['ratio']=='default' || $global_config['JETR']['ratio']=='percent')) {
                    //     $jetr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='JETR' && $global_config['JETR']['ratio']=='scale') {
                    //     $jetr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='JETR'&& $value->found_val) && $sumJETR += $jetr;
                    ($value->found_ind=='JETR'&& $value->found_divisor) && $sumJETR += floatval($value->found_divisor);
                    ($value->found_ind=='JETR'&& $value->found_divider) && $jetr += floatval($value->found_divider);
                    //---JETR
                    
                    //---JFCR
                    // if ($value->found_ind=='JFCR' && ($global_config['JFCR']['ratio']=='default' || $global_config['JFCR']['ratio']=='percent')) {
                    //     $jfcr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='JFCR' && $global_config['JFCR']['ratio']=='scale') {
                    //     $jfcr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='JFCR'&& $value->found_val) && $sumJFCR += $jfcr;
                    ($value->found_ind=='JFCR'&& $value->found_divisor) && $sumJFCR += floatval($value->found_divisor);
                    ($value->found_ind=='JFCR'&& $value->found_divider) && $jfcr += floatval($value->found_divider);
                    //-----JFCR

                    //-------JHSTR
                    // if ($value->found_ind=='JHSTR' && ($global_config['JHSTR']['ratio']=='default' || $global_config['JHSTR']['ratio']=='percent')) {
                    //     $jhstr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='JHSTR' && $global_config['JHSTR']['ratio']=='scale') {
                    //     $jhstr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='JHSTR'&& $value->found_val) && $sumJHSTR += $jhstr;
                    ($value->found_ind=='JHSTR'&& $value->found_divisor) && $sumJHSTR += floatval($value->found_divisor);
                    ($value->found_ind=='JHSTR'&& $value->found_divider) && $jhstr += floatval($value->found_divider);
                    //---JHSTR
                    
                    //-------JSBR
                    // if ($value->found_ind=='JSBR' && ($global_config['JSBR']['ratio']=='default' || $global_config['JSBR']['ratio']=='percent')) {
                    //     $jsbr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='JSBR' && $global_config['JSBR']['ratio']=='scale') {
                    //     $jsbr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='JSBR'&& $value->found_val) && $sumJSBR += $jsbr;
                    ($value->found_ind=='JSBR'&& $value->found_divisor) && $sumJSBR += floatval($value->found_divisor);
                    ($value->found_ind=='JSBR'&& $value->found_divider) && $jsbr += floatval($value->found_divider);
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
                // $res['合计'][2] = $global_config['JSTR']['ratio']=='default'? round( ($sumJSTR/$count), 3).$global_config['JSTR']['unit']:($global_config['JSTR']['ratio']=='percent'?round( ($sumJSTR/$count), 3).'%':round( ($sumJSTR/$count), 3).':1');
                $res['合计'][2] = ($jstr == 0 || $sumJSTR==0  ) ? '0' :  ($global_config['JSTR']['ratio']=='default'? round( ($sumJSTR/$jstr), 3).$global_config['JSTR']['unit']:($global_config['JSTR']['ratio']=='percent'?round( ($sumJSTR/$jstr)*100, 3).'%':round( ($sumJSTR/$jstr), 3).':1') );
                $res['合计'][3] = $global_config['JSTR']['standard_val'];
                $res['合计'][4] = $sumJSTR;
                $res['合计'][5] = $jstr;
                $res['合计'][6] = '合计';

                // $res['合计'][7] = $global_config['JETR']['ratio']=='default'? round( ($sumJETR/$count), 3).$global_config['JETR']['unit']:($global_config['JETR']['ratio']=='percent'?round( ($sumJETR/$count), 3).'%':round( ($sumJETR/$count), 3).':1');
                $res['合计'][7] = ($jetr == 0 || $sumJETR==0  ) ? '0' :  ($global_config['JETR']['ratio']=='default'? round( ($sumJETR/$jetr), 3).$global_config['JETR']['unit']:($global_config['JETR']['ratio']=='percent'?round( ($sumJETR/$jetr)*100, 3).'%':round( ($sumJETR/$jetr), 3).':1') );
                $res['合计'][8] = $global_config['JETR']['standard_val'];
                $res['合计'][9] = $sumJETR;
                $res['合计'][10] = $jetr;
                $res['合计'][11] = '合计';

                // $res['合计'][12] = $global_config['JFCR']['ratio']=='default'? round( ($sumJFCR/$count), 3).$global_config['JFCR']['unit']:($global_config['JFCR']['ratio']=='percent'?round( ($sumJFCR/$count), 3).'%':round( ($sumJFCR/$count), 3).':1');
                $res['合计'][12] =  ($jfcr == 0 || $sumJFCR==0  ) ? '0' :  ($global_config['JFCR']['ratio']=='default'? round( ($sumJFCR/$jfcr), 3).$global_config['JFCR']['unit']:($global_config['JFCR']['ratio']=='percent'?round( ($sumJFCR/$jfcr)*100, 3).'%':round( ($sumJFCR/$jfcr), 3).':1') );
                $res['合计'][13] = $global_config['JFCR']['standard_val'];
                $res['合计'][14] = $sumJFCR;
                $res['合计'][15] = $jfcr;
                $res['合计'][16] = '合计';

                // $res['合计'][17] = $global_config['JHSTR']['ratio']=='default'? round( ($sumJHSTR/$count), 3).$global_config['JHSTR']['unit']:($global_config['JHSTR']['ratio']=='percent'?round( ($sumJHSTR/$count), 3).'%':round( ($sumJHSTR/$count), 3).':1');
                $res['合计'][17] = ($jhstr == 0 || $sumJHSTR==0  ) ? '0' :  ($global_config['JHSTR']['ratio']=='default'? round( ($sumJHSTR/$jhstr), 3).$global_config['JHSTR']['unit']:($global_config['JHSTR']['ratio']=='percent'?round( ($sumJHSTR/$jhstr)*100, 3).'%':round( ($sumJHSTR/$jhstr), 3).':1') );
                $res['合计'][18] = $global_config['JHSTR']['standard_val'];
                $res['合计'][19] = $sumJHSTR;
                $res['合计'][20] = $jhstr;
                $res['合计'][21] = '合计';

                // $res['合计'][22] = $global_config['JSBR']['ratio']=='default'? round( ($sumJSBR/$count), 3).$global_config['JSBR']['unit']:($global_config['JSBR']['ratio']=='percent'?round( ($sumJSBR/$count), 3).'%':round( ($sumJSBR/$count), 3).':1');
                $res['合计'][22] = ($jsbr == 0 || $sumJSBR==0  ) ? '0' :  ($global_config['JSBR']['ratio']=='default'? round( ($sumJSBR/$jsbr), 3).$global_config['JSBR']['unit']:($global_config['JSBR']['ratio']=='percent'?round( ($sumJSBR/$jsbr)*100, 3).'%':round( ($sumJSBR/$jsbr), 3).':1') );
                $res['合计'][23] = $global_config['JSBR']['standard_val'];
                $res['合计'][24] = $sumJSBR;
                $res['合计'][25] = $jsbr;
                $res['合计'][26] = '合计';
                break;
            case 'mnineYearCon':
                $res = [];
                $index = 1;
                $sumMNPSTR = 0;
                $sumMNPTR = 0;
                $sumMNPHSTR = 0;
                $sumMNPFCR = 0;
                $sumMNPSBR = 0;
                $sumMNJSTR = 0;
                $sumMNJETR = 0;
                $sumMNJFCR = 0;
                $sumMNJHSTR = 0;
                $sumMNJSBR = 0;
                $mnpstr = 0;
                $mnptr = 0;
                $mnpfcr = 0;
                $mnphstr = 0;
                $mnjstr = 0;
                $mnpsbr = 0;
                $mnjetr = 0;
                $mnjfcr = 0;
                $mnjhstr = 0;
                $mnjsbr = 0;
                foreach ($sheetData as $value) {
                    if (in_array($value->user_id.$value->school.$value->school_type.$value->found_ind, $repeat)) {
                        continue;
                    }
                    //并发重复数据处理
                    $repeat[] = $value->user_id.$value->school.$value->school_type.$value->found_ind;
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='MNPSTR' && $res[$value->school][2] = $value->found_ind=='MNPSTR' ? $value->found_val:'';
                    $value->found_ind=='MNPSTR' && $res[$value->school][3] = $value->found_ind=='MNPSTR' ? $value->standard_val:'';
                    $value->found_ind=='MNPSTR' && $res[$value->school][4] = $value->found_ind=='MNPSTR' ? $value->found_divisor:'';
                    $value->found_ind=='MNPSTR' && $res[$value->school][5] = $value->found_ind=='MNPSTR' ? $value->found_divider:'';
                    $value->found_ind=='MNPSTR' && $res[$value->school][6] = $value->found_ind=='MNPSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    $value->found_ind=='MNPTR' && $res[$value->school][7] = $value->found_ind=='MNPTR' ? $value->found_val:'';
                    $value->found_ind=='MNPTR' && $res[$value->school][8] = $value->found_ind=='MNPTR' ? $value->standard_val:'';
                    $value->found_ind=='MNPTR' && $res[$value->school][9] = $value->found_ind=='MNPTR' ? $value->found_divisor:'';
                    $value->found_ind=='MNPTR' && $res[$value->school][10] = $value->found_ind=='MNPTR' ? $value->found_divider:'';
                    $value->found_ind=='MNPTR' && $res[$value->school][11] = $value->found_ind=='MNPTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='MNPFCR' && $res[$value->school][12] = $value->found_ind=='MNPFCR' ? $value->found_val:'';
                    $value->found_ind=='MNPFCR' && $res[$value->school][13] = $value->found_ind=='MNPFCR' ? $value->standard_val:'';
                    $value->found_ind=='MNPFCR' && $res[$value->school][14] = $value->found_ind=='MNPFCR' ? $value->found_divisor:'';
                    $value->found_ind=='MNPFCR' && $res[$value->school][15] = $value->found_ind=='MNPFCR' ? $value->found_divider:'';
                    $value->found_ind=='MNPFCR' && $res[$value->school][16] = $value->found_ind=='MNPFCR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='MNPHSTR' && $res[$value->school][17] = $value->found_ind=='MNPHSTR' ? $value->found_val:'';
                    $value->found_ind=='MNPHSTR' && $res[$value->school][18] = $value->found_ind=='MNPHSTR' ? $value->standard_val:'';
                    $value->found_ind=='MNPHSTR' && $res[$value->school][19] = $value->found_ind=='MNPHSTR' ? $value->found_divisor:'';
                    $value->found_ind=='MNPHSTR' && $res[$value->school][20] = $value->found_ind=='MNPHSTR' ? $value->found_divider:'';
                    $value->found_ind=='MNPHSTR' && $res[$value->school][21] = $value->found_ind=='MNPHSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='MNPSBR' && $res[$value->school][22] = $value->found_ind=='MNPSBR' ? $value->found_val:'';
                    $value->found_ind=='MNPSBR' && $res[$value->school][23] = $value->found_ind=='MNPSBR' ? $value->standard_val:'';
                    $value->found_ind=='MNPSBR' && $res[$value->school][24] = $value->found_ind=='MNPSBR' ? $value->found_divisor:'';
                    $value->found_ind=='MNPSBR' && $res[$value->school][25] = $value->found_ind=='MNPSBR' ? $value->found_divider:'';
                    $value->found_ind=='MNPSBR' && $res[$value->school][26] = $value->found_ind=='MNPSBR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='MNJSTR' && $res[$value->school][27] = $value->found_ind=='MNJSTR' ? $value->found_val:'';
                    $value->found_ind=='MNJSTR' && $res[$value->school][28] = $value->found_ind=='MNJSTR' ? $value->standard_val:'';
                    $value->found_ind=='MNJSTR' && $res[$value->school][29] = $value->found_ind=='MNJSTR' ? $value->found_divisor:'';
                    $value->found_ind=='MNJSTR' && $res[$value->school][30] = $value->found_ind=='MNJSTR' ? $value->found_divider:'';
                    $value->found_ind=='MNJSTR' && $res[$value->school][31] = $value->found_ind=='MNJSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    $value->found_ind=='MNJETR' && $res[$value->school][32] = $value->found_ind=='MNJETR' ? $value->found_val:'';
                    $value->found_ind=='MNJETR' && $res[$value->school][33] = $value->found_ind=='MNJETR' ? $value->standard_val:'';
                    $value->found_ind=='MNJETR' && $res[$value->school][34] = $value->found_ind=='MNJETR' ? $value->found_divisor:'';
                    $value->found_ind=='MNJETR' && $res[$value->school][35] = $value->found_ind=='MNJETR' ? $value->found_divider:'';
                    $value->found_ind=='MNJETR' && $res[$value->school][36] = $value->found_ind=='MNJETR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='MNJFCR' && $res[$value->school][37] = $value->found_ind=='MNJFCR' ? $value->found_val:'';
                    $value->found_ind=='MNJFCR' && $res[$value->school][38] = $value->found_ind=='MNJFCR' ? $value->standard_val:'';
                    $value->found_ind=='MNJFCR' && $res[$value->school][39] = $value->found_ind=='MNJFCR' ? $value->found_divisor:'';
                    $value->found_ind=='MNJFCR' && $res[$value->school][40] = $value->found_ind=='MNJFCR' ? $value->found_divider:'';
                    $value->found_ind=='MNJFCR' && $res[$value->school][41] = $value->found_ind=='MNJFCR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='MNJHSTR' && $res[$value->school][42] = $value->found_ind=='MNJHSTR' ? $value->found_val:'';
                    $value->found_ind=='MNJHSTR' && $res[$value->school][43] = $value->found_ind=='MNJHSTR' ? $value->standard_val:'';
                    $value->found_ind=='MNJHSTR' && $res[$value->school][44] = $value->found_ind=='MNJHSTR' ? $value->found_divisor:'';
                    $value->found_ind=='MNJHSTR' && $res[$value->school][45] = $value->found_ind=='MNJHSTR' ? $value->found_divider:'';
                    $value->found_ind=='MNJHSTR' && $res[$value->school][46] = $value->found_ind=='MNJHSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='MNJSBR' && $res[$value->school][47] = $value->found_ind=='MNJSBR' ? $value->found_val:'';
                    $value->found_ind=='MNJSBR' && $res[$value->school][48] = $value->found_ind=='MNJSBR' ? $value->standard_val:'';
                    $value->found_ind=='MNJSBR' && $res[$value->school][49] = $value->found_ind=='MNJSBR' ? $value->found_divisor:'';
                    $value->found_ind=='MNJSBR' && $res[$value->school][50] = $value->found_ind=='MNJSBR' ? $value->found_divider:'';
                    $value->found_ind=='MNJSBR' && $res[$value->school][51] = $value->found_ind=='MNJSBR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    //------MNPSTR
                    // if ($value->found_ind=='MNPSTR' && ($global_config['MNPSTR']['ratio']=='default' || $global_config['MNPSTR']['ratio']=='percent')) {
                    //     $mnpstr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MNPSTR' && $global_config['MNPSTR']['ratio']=='scale') {
                    //     $mnpstr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MNPSTR'&& $value->found_val) && $sumMNPSTR += $mnpstr;
                    ($value->found_ind=='MNPSTR'&& $value->found_divisor) && $sumMNPSTR += floatval($value->found_divisor);
                    ($value->found_ind=='MNPSTR'&& $value->found_divider) && $mnpstr += floatval($value->found_divider);
                    //---MNPSTR
                    //
                    //MNPTR
                    // if ($value->found_ind=='MNPTR' && ($global_config['MNPTR']['ratio']=='default' || $global_config['MNPTR']['ratio']=='percent')) {
                    //     $mnptr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MNPTR' && $global_config['MNPTR']['ratio']=='scale') {
                    //     $mnptr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MNPTR'&& $value->found_val) && $sumMNPTR += $mnptr;
                    ($value->found_ind=='MNPTR'&& $value->found_divisor) && $sumMNPTR += floatval($value->found_divisor);
                    ($value->found_ind=='MNPTR'&& $value->found_divider) && $mnptr += floatval($value->found_divider);
                    //---MNPTR
                    
                    //---MNPFCR
                    // if ($value->found_ind=='MNPFCR' && ($global_config['MNPFCR']['ratio']=='default' || $global_config['MNPFCR']['ratio']=='percent')) {
                    //     $mnpfcr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MNPFCR' && $global_config['MNPFCR']['ratio']=='scale') {
                    //     $mnpfcr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MNPFCR'&& $value->found_val) && $sumMNPFCR += $mnpfcr;
                    ($value->found_ind=='MNPFCR'&& $value->found_divisor) && $sumMNPFCR += floatval($value->found_divisor);
                    ($value->found_ind=='MNPFCR'&& $value->found_divider) && $mnpfcr += floatval($value->found_divider);
                    //-----MNPFCR

                    //-------MNPHSTR
                    // if ($value->found_ind=='MNPHSTR' && ($global_config['MNPHSTR']['ratio']=='default' || $global_config['MNPHSTR']['ratio']=='percent')) {
                    //     $mnphstr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MNPHSTR' && $global_config['MNPHSTR']['ratio']=='scale') {
                    //     $mnphstr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MNPHSTR'&& $value->found_val) && $sumMNPHSTR += $mnphstr;
                    ($value->found_ind=='MNPHSTR'&& $value->found_divisor) && $sumMNPHSTR += floatval($value->found_divisor);
                    ($value->found_ind=='MNPHSTR'&& $value->found_divider) && $mnphstr += floatval($value->found_divider);
                    //---MNPHSTR
                    
                    //-------MNPSBR
                    // if ($value->found_ind=='MNPSBR' && ($global_config['MNPSBR']['ratio']=='default' || $global_config['MNPSBR']['ratio']=='percent')) {
                    //     $mnpsbr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MNPSBR' && $global_config['MNPSBR']['ratio']=='scale') {
                    //     $mnpsbr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MNPSBR'&& $value->found_val) && $sumMNPSBR += $mnpsbr;
                    ($value->found_ind=='MNPSBR'&& $value->found_divisor) && $sumMNPSBR += floatval($value->found_divisor);
                    ($value->found_ind=='MNPSBR'&& $value->found_divider) && $mnpsbr += floatval($value->found_divider);
                    //---MNPSBR
                    

                    //------MNJSTR
                    // if ($value->found_ind=='MNJSTR' && ($global_config['MNJSTR']['ratio']=='default' || $global_config['MNJSTR']['ratio']=='percent')) {
                    //     $mnjstr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MNJSTR' && $global_config['MNJSTR']['ratio']=='scale') {
                    //     $mnjstr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MNJSTR'&& $value->found_val) && $sumMNJSTR += $mnjstr;
                    ($value->found_ind=='MNJSTR'&& $value->found_divisor) && $sumMNJSTR += floatval($value->found_divisor);
                    ($value->found_ind=='MNJSTR'&& $value->found_divider) && $mnjstr += floatval($value->found_divider);
                    //---MNJSTR
                    //
                    //MNJETR
                    // if ($value->found_ind=='MNJETR' && ($global_config['MNJETR']['ratio']=='default' || $global_config['MNJETR']['ratio']=='percent')) {
                    //     $mnjetr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MNJETR' && $global_config['MNJETR']['ratio']=='scale') {
                    //     $mnjetr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MNJETR'&& $value->found_val) && $sumMNJETR += $mnjetr;
                    ($value->found_ind=='MNJETR'&& $value->found_divisor) && $sumMNJETR += floatval($value->found_divisor);
                    ($value->found_ind=='MNJETR'&& $value->found_divider) && $mnjetr += floatval($value->found_divider);
                    //---MNJETR
                    
                    //---MNJFCR
                    // if ($value->found_ind=='MNJFCR' && ($global_config['MNJFCR']['ratio']=='default' || $global_config['MNJFCR']['ratio']=='percent')) {
                    //     $mnjfcr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MNJFCR' && $global_config['MNJFCR']['ratio']=='scale') {
                    //     $mnjfcr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MNJFCR'&& $value->found_val) && $sumMNJFCR += $mnjfcr;
                    ($value->found_ind=='MNJFCR'&& $value->found_divisor) && $sumMNJFCR += floatval($value->found_divisor);
                    ($value->found_ind=='MNJFCR'&& $value->found_divider) && $mnjfcr += floatval($value->found_divider);
                    //-----MNJFCR

                    //-------MNJHSTR
                    // if ($value->found_ind=='MNJHSTR' && ($global_config['MNJHSTR']['ratio']=='default' || $global_config['MNJHSTR']['ratio']=='percent')) {
                    //     $mnjhstr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MNJHSTR' && $global_config['MNJHSTR']['ratio']=='scale') {
                    //     $mnjhstr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MNJHSTR'&& $value->found_val) && $sumMNJHSTR += $mnjhstr;
                    ($value->found_ind=='MNJHSTR'&& $value->found_divisor) && $sumMNJHSTR += floatval($value->found_divisor);
                    ($value->found_ind=='MNJHSTR'&& $value->found_divider) && $mnjhstr += floatval($value->found_divider);
                    //---MNJHSTR
                    
                    //-------MNJSBR
                    // if ($value->found_ind=='MNJSBR' && ($global_config['MNJSBR']['ratio']=='default' || $global_config['MNJSBR']['ratio']=='percent')) {
                    //     $mnjsbr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MNJSBR' && $global_config['MNJSBR']['ratio']=='scale') {
                    //     $mnjsbr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MNJSBR'&& $value->found_val) && $sumMNJSBR += $mnjsbr;
                    ($value->found_ind=='MNJSBR'&& $value->found_divisor) && $sumMNJSBR += floatval($value->found_divisor);
                    ($value->found_ind=='MNJSBR'&& $value->found_divider) && $mnjsbr += floatval($value->found_divider);
                    //---MNJSBR
                    $index++;
                }
                if (!$res) {
                    break;
                }
                $count = count($res);
                //合计
                $res['合计'][0] = '';
                $res['合计'][1] = '合计';
                // $res['合计'][2] = $global_config['MNPSTR']['ratio']=='default'? round( ($sumMNPSTR/$count), 3).$global_config['MNPSTR']['unit']:($global_config['MNPSTR']['ratio']=='percent'?round( ($sumMNPSTR/$count), 3).'%':round( ($sumMNPSTR/$count), 3).':1');
                $res['合计'][2] = ($mnpstr == 0 || $sumMNPSTR==0  ) ? '0' :  ($global_config['MNPSTR']['ratio']=='default'? round( ($sumMNPSTR/$mnpstr), 3).$global_config['MNPSTR']['unit']:($global_config['MNPSTR']['ratio']=='percent'?round( ($sumMNPSTR/$mnpstr)*100, 3).'%':round( ($sumMNPSTR/$mnpstr), 3).':1') );
                $res['合计'][3] = $global_config['MNPSTR']['standard_val'];
                $res['合计'][4] = $sumMNPSTR;
                $res['合计'][5] = $mnpstr;
                $res['合计'][6] = '合计';

                // $res['合计'][7] = $global_config['MNPTR']['ratio']=='default'? round( ($sumMNPTR/$count), 3).$global_config['MNPTR']['unit']:($global_config['MNPTR']['ratio']=='percent'?round( ($sumMNPTR/$count), 3).'%':round( ($sumMNPTR/$count), 3).':1');
                $res['合计'][7] =  ($mnptr == 0 || $sumMNPTR==0  ) ? '0' :  ($global_config['MNPTR']['ratio']=='default'? round( ($sumMNPTR/$mnptr), 3).$global_config['MNPTR']['unit']:($global_config['MNPTR']['ratio']=='percent'?round( ($sumMNPTR/$mnptr)*100, 3).'%':round( ($sumMNPTR/$mnptr), 3).':1') );
                $res['合计'][8] = $global_config['MNPTR']['standard_val'];
                $res['合计'][9] = $sumMNPTR;
                $res['合计'][10] = $mnptr;
                $res['合计'][11] = '合计';

                // $res['合计'][12] = $global_config['MNPFCR']['ratio']=='default'? round( ($sumMNPFCR/$count), 3).$global_config['MNPFCR']['unit']:($global_config['MNPFCR']['ratio']=='percent'?round( ($sumMNPFCR/$count), 3).'%':round( ($sumMNPFCR/$count), 3).':1');
                $res['合计'][12] = ($mnpfcr == 0 || $sumMNPFCR==0  ) ? '0' :  ($global_config['MNPFCR']['ratio']=='default'? round( ($sumMNPFCR/$mnpfcr), 3).$global_config['MNPFCR']['unit']:($global_config['MNPFCR']['ratio']=='percent'?round( ($sumMNPFCR/$mnpfcr)*100, 3).'%':round( ($sumMNPFCR/$mnpfcr), 3).':1') );
                $res['合计'][13] = $global_config['MNPFCR']['standard_val'];
                $res['合计'][14] = $sumMNPFCR;
                $res['合计'][15] = $mnpfcr;
                $res['合计'][16] = '合计';

                // $res['合计'][17] = $global_config['MNPHSTR']['ratio']=='default'? round( ($sumMNPHSTR/$count), 3).$global_config['MNPHSTR']['unit']:($global_config['MNPHSTR']['ratio']=='percent'?round( ($sumMNPHSTR/$count), 3).'%':round( ($sumMNPHSTR/$count), 3).':1');
                $res['合计'][17] = ($mnphstr == 0 || $sumMNPHSTR==0  ) ? '0' :  ($global_config['MNPHSTR']['ratio']=='default'? round( ($sumMNPHSTR/$mnphstr), 3).$global_config['MNPHSTR']['unit']:($global_config['MNPHSTR']['ratio']=='percent'?round( ($sumMNPHSTR/$mnphstr)*100, 3).'%':round( ($sumMNPHSTR/$mnphstr), 3).':1') );
                $res['合计'][18] = $global_config['MNPHSTR']['standard_val'];
                $res['合计'][19] = $sumMNPHSTR;
                $res['合计'][20] = $mnphstr;
                $res['合计'][21] = '合计';

                // $res['合计'][22] = $global_config['MNPSBR']['ratio']=='default'? round( ($sumMNPSBR/$count), 3).$global_config['MNPSBR']['unit']:($global_config['MNPSBR']['ratio']=='percent'?round( ($sumMNPSBR/$count), 3).'%':round( ($sumMNPSBR/$count), 3).':1');
                $res['合计'][22] =  ($mnpsbr == 0 || $sumMNPSBR==0  ) ? '0' :  ($global_config['MNPSBR']['ratio']=='default'? round( ($sumMNPSBR/$mnpsbr), 3).$global_config['MNPSBR']['unit']:($global_config['MNPSBR']['ratio']=='percent'?round( ($sumMNPSBR/$mnpsbr)*100, 3).'%':round( ($sumMNPSBR/$mnpsbr), 3).':1') );
                $res['合计'][23] = $global_config['MNPSBR']['standard_val'];
                $res['合计'][24] = $sumMNPSBR;
                $res['合计'][25] = $mnpsbr;
                $res['合计'][26] = '合计';
                // $res['合计'][27] = $global_config['MNJSTR']['ratio']=='default'? round( ($sumMNJSTR/$count), 3).$global_config['MNJSTR']['unit']:($global_config['MNJSTR']['ratio']=='percent'?round( ($sumMNJSTR/$count), 3).'%':round( ($sumMNJSTR/$count), 3).':1');
                $res['合计'][27] = ($mnjstr == 0 || $sumMNJSTR==0  ) ? '0' :  ($global_config['MNJSTR']['ratio']=='default'? round( ($sumMNJSTR/$mnjstr), 3).$global_config['MNJSTR']['unit']:($global_config['MNJSTR']['ratio']=='percent'?round( ($sumMNJSTR/$mnjstr)*100, 3).'%':round( ($sumMNJSTR/$mnjstr), 3).':1') );
                $res['合计'][28] = $global_config['MNJSTR']['standard_val'];
                $res['合计'][29] = $sumMNJSTR;
                $res['合计'][30] = $mnjstr;
                $res['合计'][31] = '合计';

                // $res['合计'][32] = $global_config['MNJETR']['ratio']=='default'? round( ($sumMNJETR/$count), 3).$global_config['MNJETR']['unit']:($global_config['MNJETR']['ratio']=='percent'?round( ($sumMNJETR/$count), 3).'%':round( ($sumMNJETR/$count), 3).':1');
                $res['合计'][32] = ($mnjetr == 0 || $sumMNJETR==0  ) ? '0' :  ($global_config['MNJETR']['ratio']=='default'? round( ($sumMNJETR/$mnjetr), 3).$global_config['MNJETR']['unit']:($global_config['MNJETR']['ratio']=='percent'?round( ($sumMNJETR/$mnjetr)*100, 3).'%':round( ($sumMNJETR/$mnjetr), 3).':1') );
                $res['合计'][33] = $global_config['MNJETR']['standard_val'];
                $res['合计'][34] = $sumMNJETR;
                $res['合计'][35] = $mnjetr;
                $res['合计'][36] = '合计';

                // $res['合计'][37] = $global_config['MNJFCR']['ratio']=='default'? round( ($sumMNJFCR/$count), 3).$global_config['MNJFCR']['unit']:($global_config['MNJFCR']['ratio']=='percent'?round( ($sumMNJFCR/$count), 3).'%':round( ($sumMNJFCR/$count), 3).':1');
                $res['合计'][37] = ($mnjfcr == 0 || $sumMNJFCR==0  ) ? '0' :  ($global_config['MNJFCR']['ratio']=='default'? round( ($sumMNJFCR/$mnjfcr), 3).$global_config['MNJFCR']['unit']:($global_config['MNJFCR']['ratio']=='percent'?round( ($sumMNJFCR/$mnjfcr)*100, 3).'%':round( ($sumMNJFCR/$mnjfcr), 3).':1') );
                $res['合计'][38] = $global_config['MNJFCR']['standard_val'];
                $res['合计'][39] = $sumMNJFCR;
                $res['合计'][40] = $mnjfcr;
                $res['合计'][41] = '合计';

                // $res['合计'][42] = $global_config['MNJHSTR']['ratio']=='default'? round( ($sumMNJHSTR/$count), 3).$global_config['MNJHSTR']['unit']:($global_config['MNJHSTR']['ratio']=='percent'?round( ($sumMNJHSTR/$count), 3).'%':round( ($sumMNJHSTR/$count), 3).':1');
                $res['合计'][42] = ($mnjhstr == 0 || $sumMNJHSTR==0  ) ? '0' :  ($global_config['MNJHSTR']['ratio']=='default'? round( ($sumMNJHSTR/$mnjhstr), 3).$global_config['MNJHSTR']['unit']:($global_config['MNJHSTR']['ratio']=='percent'?round( ($sumMNJHSTR/$mnjhstr)*100, 3).'%':round( ($sumMNJHSTR/$mnjhstr), 3).':1') );
                $res['合计'][43] = $global_config['MNJHSTR']['standard_val'];
                $res['合计'][44] = $sumMNJHSTR;
                $res['合计'][45] = $mnjhstr;
                $res['合计'][46] = '合计';

                // $res['合计'][47] = $global_config['MNJSBR']['ratio']=='default'? round( ($sumMNJSBR/$count), 3).$global_config['MNJSBR']['unit']:($global_config['MNJSBR']['ratio']=='percent'?round( ($sumMNJSBR/$count), 3).'%':round( ($sumMNJSBR/$count), 3).':1');
                $res['合计'][47] = ($mnjsbr == 0 || $sumMNJSBR==0  ) ? '0' :  ($global_config['MNJSBR']['ratio']=='default'? round( ($sumMNJSBR/$mnjsbr), 3).$global_config['MNJSBR']['unit']:($global_config['MNJSBR']['ratio']=='percent'?round( ($sumMNJSBR/$mnjsbr)*100, 3).'%':round( ($sumMNJSBR/$mnjsbr), 3).':1') );
                $res['合计'][48] = $global_config['MNJSBR']['standard_val'];
                $res['合计'][49] = $sumMNJSBR;
                $res['合计'][50] = $mnjsbr;
                $res['合计'][51] = '合计';
                break;

            case 'mtwelveYearCon':
                $res = [];
                $index = 1;
                $sumMTPSTR = 0;
                $sumMTPTR = 0;
                $sumMTPHSTR = 0;
                $sumMTPFCR = 0;
                $sumMTPSBR = 0;
                $sumMTJSTR = 0;
                $sumMTJETR = 0;
                $sumMTJFCR = 0;
                $sumMTJHSTR = 0;
                $sumMTJSBR = 0;
                $sumMTHSTR = 0;
                $sumMTHETR = 0;
                $sumMTHSMR = 0;
                $mtpstr = 0;
                $mtptr = 0;
                $mtpfcr = 0;
                $mtphstr = 0;
                $mtpsbr = 0;
                $mtjstr = 0;
                $mtjetr = 0;
                $mtjfcr = 0;
                $mtjhstr = 0;
                $mtjsbr = 0;
                $mthstr = 0;
                $mthetr = 0;
                $mthsmr = 0;
                foreach ($sheetData as $value) {
                    if (in_array($value->user_id.$value->school.$value->school_type.$value->found_ind, $repeat)) {
                        continue;
                    }
                    //并发重复数据处理
                    $repeat[] = $value->user_id.$value->school.$value->school_type.$value->found_ind;

                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='MTPSTR' && $res[$value->school][2] = $value->found_ind=='MTPSTR' ? $value->found_val:'';
                    $value->found_ind=='MTPSTR' && $res[$value->school][3] = $value->found_ind=='MTPSTR' ? $value->standard_val:'';
                    $value->found_ind=='MTPSTR' && $res[$value->school][4] = $value->found_ind=='MTPSTR' ? $value->found_divisor:'';
                    $value->found_ind=='MTPSTR' && $res[$value->school][5] = $value->found_ind=='MTPSTR' ? $value->found_divider:'';
                    $value->found_ind=='MTPSTR' && $res[$value->school][6] = $value->found_ind=='MTPSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    $value->found_ind=='MTPTR' && $res[$value->school][7] = $value->found_ind=='MTPTR' ? $value->found_val:'';
                    $value->found_ind=='MTPTR' && $res[$value->school][8] = $value->found_ind=='MTPTR' ? $value->standard_val:'';
                    $value->found_ind=='MTPTR' && $res[$value->school][9] = $value->found_ind=='MTPTR' ? $value->found_divisor:'';
                    $value->found_ind=='MTPTR' && $res[$value->school][10] = $value->found_ind=='MTPTR' ? $value->found_divider:'';
                    $value->found_ind=='MTPTR' && $res[$value->school][11] = $value->found_ind=='MTPTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='MTPFCR' && $res[$value->school][12] = $value->found_ind=='MTPFCR' ? $value->found_val:'';
                    $value->found_ind=='MTPFCR' && $res[$value->school][13] = $value->found_ind=='MTPFCR' ? $value->standard_val:'';
                    $value->found_ind=='MTPFCR' && $res[$value->school][14] = $value->found_ind=='MTPFCR' ? $value->found_divisor:'';
                    $value->found_ind=='MTPFCR' && $res[$value->school][15] = $value->found_ind=='MTPFCR' ? $value->found_divider:'';
                    $value->found_ind=='MTPFCR' && $res[$value->school][16] = $value->found_ind=='MTPFCR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='MTPHSTR' && $res[$value->school][17] = $value->found_ind=='MTPHSTR' ? $value->found_val:'';
                    $value->found_ind=='MTPHSTR' && $res[$value->school][18] = $value->found_ind=='MTPHSTR' ? $value->standard_val:'';
                    $value->found_ind=='MTPHSTR' && $res[$value->school][19] = $value->found_ind=='MTPHSTR' ? $value->found_divisor:'';
                    $value->found_ind=='MTPHSTR' && $res[$value->school][20] = $value->found_ind=='MTPHSTR' ? $value->found_divider:'';
                    $value->found_ind=='MTPHSTR' && $res[$value->school][21] = $value->found_ind=='MTPHSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='MTPSBR' && $res[$value->school][22] = $value->found_ind=='MTPSBR' ? $value->found_val:'';
                    $value->found_ind=='MTPSBR' && $res[$value->school][23] = $value->found_ind=='MTPSBR' ? $value->standard_val:'';
                    $value->found_ind=='MTPSBR' && $res[$value->school][24] = $value->found_ind=='MTPSBR' ? $value->found_divisor:'';
                    $value->found_ind=='MTPSBR' && $res[$value->school][25] = $value->found_ind=='MTPSBR' ? $value->found_divider:'';
                    $value->found_ind=='MTPSBR' && $res[$value->school][26] = $value->found_ind=='MTPSBR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='MTJSTR' && $res[$value->school][27] = $value->found_ind=='MTJSTR' ? $value->found_val:'';
                    $value->found_ind=='MTJSTR' && $res[$value->school][28] = $value->found_ind=='MTJSTR' ? $value->standard_val:'';
                    $value->found_ind=='MTJSTR' && $res[$value->school][29] = $value->found_ind=='MTJSTR' ? $value->found_divisor:'';
                    $value->found_ind=='MTJSTR' && $res[$value->school][30] = $value->found_ind=='MTJSTR' ? $value->found_divider:'';
                    $value->found_ind=='MTJSTR' && $res[$value->school][31] = $value->found_ind=='MTJSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    $value->found_ind=='MTJETR' && $res[$value->school][32] = $value->found_ind=='MTJETR' ? $value->found_val:'';
                    $value->found_ind=='MTJETR' && $res[$value->school][33] = $value->found_ind=='MTJETR' ? $value->standard_val:'';
                    $value->found_ind=='MTJETR' && $res[$value->school][34] = $value->found_ind=='MTJETR' ? $value->found_divisor:'';
                    $value->found_ind=='MTJETR' && $res[$value->school][35] = $value->found_ind=='MTJETR' ? $value->found_divider:'';
                    $value->found_ind=='MTJETR' && $res[$value->school][36] = $value->found_ind=='MTJETR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='MTJFCR' && $res[$value->school][37] = $value->found_ind=='MTJFCR' ? $value->found_val:'';
                    $value->found_ind=='MTJFCR' && $res[$value->school][38] = $value->found_ind=='MTJFCR' ? $value->standard_val:'';
                    $value->found_ind=='MTJFCR' && $res[$value->school][39] = $value->found_ind=='MTJFCR' ? $value->found_divisor:'';
                    $value->found_ind=='MTJFCR' && $res[$value->school][40] = $value->found_ind=='MTJFCR' ? $value->found_divider:'';
                    $value->found_ind=='MTJFCR' && $res[$value->school][41] = $value->found_ind=='MTJFCR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='MTJHSTR' && $res[$value->school][42] = $value->found_ind=='MTJHSTR' ? $value->found_val:'';
                    $value->found_ind=='MTJHSTR' && $res[$value->school][43] = $value->found_ind=='MTJHSTR' ? $value->standard_val:'';
                    $value->found_ind=='MTJHSTR' && $res[$value->school][44] = $value->found_ind=='MTJHSTR' ? $value->found_divisor:'';
                    $value->found_ind=='MTJHSTR' && $res[$value->school][45] = $value->found_ind=='MTJHSTR' ? $value->found_divider:'';
                    $value->found_ind=='MTJHSTR' && $res[$value->school][46] = $value->found_ind=='MTJHSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='MTJSBR' && $res[$value->school][47] = $value->found_ind=='MTJSBR' ? $value->found_val:'';
                    $value->found_ind=='MTJSBR' && $res[$value->school][48] = $value->found_ind=='MTJSBR' ? $value->standard_val:'';
                    $value->found_ind=='MTJSBR' && $res[$value->school][49] = $value->found_ind=='MTJSBR' ? $value->found_divisor:'';
                    $value->found_ind=='MTJSBR' && $res[$value->school][50] = $value->found_ind=='MTJSBR' ? $value->found_divider:'';
                    $value->found_ind=='MTJSBR' && $res[$value->school][51] = $value->found_ind=='MTJSBR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='MTHSTR' && $res[$value->school][52] = $value->found_ind=='MTHSTR' ? $value->found_val:'';
                    $value->found_ind=='MTHSTR' && $res[$value->school][53] = $value->found_ind=='MTHSTR' ? $value->standard_val:'';
                    $value->found_ind=='MTHSTR' && $res[$value->school][54] = $value->found_ind=='MTHSTR' ? $value->found_divisor:'';
                    $value->found_ind=='MTHSTR' && $res[$value->school][55] = $value->found_ind=='MTHSTR' ? $value->found_divider:'';
                    $value->found_ind=='MTHSTR' && $res[$value->school][56] = $value->found_ind=='MTHSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    $value->found_ind=='MTHETR' && $res[$value->school][57] = $value->found_ind=='MTHETR' ? $value->found_val:'';
                    $value->found_ind=='MTHETR' && $res[$value->school][58] = $value->found_ind=='MTHETR' ? $value->standard_val:'';
                    $value->found_ind=='MTHETR' && $res[$value->school][59] = $value->found_ind=='MTHETR' ? $value->found_divisor:'';
                    $value->found_ind=='MTHETR' && $res[$value->school][60] = $value->found_ind=='MTHETR' ? $value->found_divider:'';
                    $value->found_ind=='MTHETR' && $res[$value->school][61] = $value->found_ind=='MTHETR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='MTHSMR' && $res[$value->school][62] = $value->found_ind=='MTHSMR' ? $value->found_val:'';
                    $value->found_ind=='MTHSMR' && $res[$value->school][63] = $value->found_ind=='MTHSMR' ? $value->standard_val:'';
                    $value->found_ind=='MTHSMR' && $res[$value->school][64] = $value->found_ind=='MTHSMR' ? $value->found_divisor:'';
                    $value->found_ind=='MTHSMR' && $res[$value->school][65] = $value->found_ind=='MTHSMR' ? $value->found_divider:'';
                    $value->found_ind=='MTHSMR' && $res[$value->school][66] = $value->found_ind=='MTHSMR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    //------MTPSTR
                    // if ($value->found_ind=='MTPSTR' && ($global_config['MTPSTR']['ratio']=='default' || $global_config['MTPSTR']['ratio']=='percent')) {
                    //     $mtpstr = floatval($value->found_val);
                    //     Log::info('mtwelveYearCon mtpstr 1'.$mtpstr);
                    // }
                    // if ($value->found_ind=='MTPSTR' && $global_config['MTPSTR']['ratio']=='scale') {
                    //     $mtpstr = explode(':', $value->found_val)[0];
                    //     Log::info('mtwelveYearCon mtpstr 2'.$mtpstr);
                    // }
                    // ($value->found_ind=='MTPSTR'&& $value->found_val) && $sumMTPSTR += $mtpstr;
                    ($value->found_ind=='MTPSTR'&& $value->found_divisor) && $sumMTPSTR += floatval($value->found_divisor);
                    ($value->found_ind=='MTPSTR'&& $value->found_divider) && $mtpstr += floatval($value->found_divider);
                    //---MTPSTR
                    //
                    //MTPTR
                    // if ($value->found_ind=='MTPTR' && ($global_config['MTPTR']['ratio']=='default' || $global_config['MTPTR']['ratio']=='percent')) {
                    //     $mtptr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MTPTR' && $global_config['MTPTR']['ratio']=='scale') {
                    //     $mtptr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MTPTR'&& $value->found_val) && $sumMTPTR += $mtptr;
                    ($value->found_ind=='MTPTR'&& $value->found_divisor) && $sumMTPTR += floatval($value->found_divisor);
                    ($value->found_ind=='MTPTR'&& $value->found_divider) && $mtptr += floatval($value->found_divider);
                    //---MTPTR
                    
                    //---MTPFCR
                    // if ($value->found_ind=='MTPFCR' && ($global_config['MTPFCR']['ratio']=='default' || $global_config['MTPFCR']['ratio']=='percent')) {
                    //     $mtpfcr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MTPFCR' && $global_config['MTPFCR']['ratio']=='scale') {
                    //     $mtpfcr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MTPFCR'&& $value->found_val) && $sumMTPFCR += $mtpfcr;
                    ($value->found_ind=='MTPFCR'&& $value->found_divisor) && $sumMTPFCR += floatval($value->found_divisor);
                    ($value->found_ind=='MTPFCR'&& $value->found_divider) && $mtpfcr += floatval($value->found_divider);
                    //-----MTPFCR

                    //-------MTPHSTR
                    // if ($value->found_ind=='MTPHSTR' && ($global_config['MTPHSTR']['ratio']=='default' || $global_config['MTPHSTR']['ratio']=='percent')) {
                    //     $mtphstr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MTPHSTR' && $global_config['MTPHSTR']['ratio']=='scale') {
                    //     $mtphstr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MTPHSTR'&& $value->found_val) && $sumMTPHSTR += $mtphstr;
                    ($value->found_ind=='MTPHSTR'&& $value->found_divisor) && $sumMTPHSTR += floatval($value->found_divisor);
                    ($value->found_ind=='MTPHSTR'&& $value->found_divider) && $mtphstr += floatval($value->found_divider);
                    //---MTPHSTR
                    
                    //-------MTPSBR
                    // if ($value->found_ind=='MTPSBR' && ($global_config['MTPSBR']['ratio']=='default' || $global_config['MTPSBR']['ratio']=='percent')) {
                    //     $mtpsbr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MTPSBR' && $global_config['MTPSBR']['ratio']=='scale') {
                    //     $mtpsbr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MTPSBR'&& $value->found_val) && $sumMTPSBR += $mtpsbr;
                    ($value->found_ind=='MTPSBR'&& $value->found_divisor) && $sumMTPSBR += floatval($value->found_divisor);
                    ($value->found_ind=='MTPSBR'&& $value->found_divider) && $mtpsbr += floatval($value->found_divider);
                    //---MTPSBR
                    

                    //------MTJSTR
                    // if ($value->found_ind=='MTJSTR' && ($global_config['MTJSTR']['ratio']=='default' || $global_config['MTJSTR']['ratio']=='percent')) {
                    //     $mtjstr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MTJSTR' && $global_config['MTJSTR']['ratio']=='scale') {
                    //     $mtjstr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MTJSTR'&& $value->found_val) && $sumMTJSTR += $mtjstr;
                    ($value->found_ind=='MTJSTR'&& $value->found_divisor) && $sumMTJSTR += floatval($value->found_divisor);
                    ($value->found_ind=='MTJSTR'&& $value->found_divider) && $mtjstr += floatval($value->found_divider);
                    //---MTJSTR
                    //
                    //MTJETR
                    // if ($value->found_ind=='MTJETR' && ($global_config['MTJETR']['ratio']=='default' || $global_config['MTJETR']['ratio']=='percent')) {
                    //     $mtjetr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MTJETR' && $global_config['MTJETR']['ratio']=='scale') {
                    //     $mtjetr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MTJETR'&& $value->found_val) && $sumMTJETR += $mtjetr;
                    ($value->found_ind=='MTJETR'&& $value->found_divisor) && $sumMTJETR += floatval($value->found_divisor);
                    ($value->found_ind=='MTJETR'&& $value->found_divider) && $mtjetr += floatval($value->found_divider);
                    //---MTJETR
                    
                    //---MTJFCR
                    // if ($value->found_ind=='MTJFCR' && ($global_config['MTJFCR']['ratio']=='default' || $global_config['MTJFCR']['ratio']=='percent')) {
                    //     $mtjfcr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MTJFCR' && $global_config['MTJFCR']['ratio']=='scale') {
                    //     $mtjfcr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MTJFCR'&& $value->found_val) && $sumMTJFCR += $mtjfcr;
                    ($value->found_ind=='MTJFCR'&& $value->found_divisor) && $sumMTJFCR += floatval($value->found_divisor);
                    ($value->found_ind=='MTJFCR'&& $value->found_divider) && $mtjfcr += floatval($value->found_divider);
                    //-----MTJFCR

                    //-------MTJHSTR
                    // if ($value->found_ind=='MTJHSTR' && ($global_config['MTJHSTR']['ratio']=='default' || $global_config['MTJHSTR']['ratio']=='percent')) {
                    //     $mtjhstr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MTJHSTR' && $global_config['MTJHSTR']['ratio']=='scale') {
                    //     $mtjhstr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MTJHSTR'&& $value->found_val) && $sumMTJHSTR += $mtjhstr;
                    ($value->found_ind=='MTJHSTR'&& $value->found_divisor) && $sumMTJHSTR += floatval($value->found_divisor);
                    ($value->found_ind=='MTJHSTR'&& $value->found_divider) && $mtjhstr += floatval($value->found_divider);
                    //---MTJHSTR
                    
                    //-------MTJSBR
                    // if ($value->found_ind=='MTJSBR' && ($global_config['MTJSBR']['ratio']=='default' || $global_config['MTJSBR']['ratio']=='percent')) {
                    //     $mtjsbr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MTJSBR' && $global_config['MTJSBR']['ratio']=='scale') {
                    //     $mtjsbr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MTJSBR'&& $value->found_val) && $sumMTJSBR += $mtjsbr;
                    ($value->found_ind=='MTJSBR'&& $value->found_divisor) && $sumMTJSBR += floatval($value->found_divisor);
                    ($value->found_ind=='MTJSBR'&& $value->found_divider) && $mtjsbr += floatval($value->found_divider);
                    //---MTJSBR
                    //------MTHSTR
                    // if ($value->found_ind=='MTHSTR' && ($global_config['MTHSTR']['ratio']=='default' || $global_config['MTHSTR']['ratio']=='percent')) {
                    //     $mthstr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MTHSTR' && $global_config['MTHSTR']['ratio']=='scale') {
                    //     $mthstr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MTHSTR'&& $value->found_val) && $sumMTHSTR += $mthstr;
                    ($value->found_ind=='MTHSTR'&& $value->found_divisor) && $sumMTHSTR += floatval($value->found_divisor);
                    ($value->found_ind=='MTHSTR'&& $value->found_divider) && $mthstr += floatval($value->found_divider);
                    //---MTHSTR
                    //
                    //MTHETR
                    // if ($value->found_ind=='MTHETR' && ($global_config['MTHETR']['ratio']=='default' || $global_config['MTHETR']['ratio']=='percent')) {
                    //     $mthetr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MTHETR' && $global_config['MTHETR']['ratio']=='scale') {
                    //     $mthetr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MTHETR'&& $value->found_val) && $sumMTHETR += $mthetr;
                    ($value->found_ind=='MTHETR'&& $value->found_divisor) && $sumMTHETR += floatval($value->found_divisor);
                    ($value->found_ind=='MTHETR'&& $value->found_divider) && $mthetr += floatval($value->found_divider);
                    //---MTHETR
                    
                    //---MTHSMR
                    // if ($value->found_ind=='MTHSMR' && ($global_config['MTHSMR']['ratio']=='default' || $global_config['MTHSMR']['ratio']=='percent')) {
                    //     $mthsmr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='MTHSMR' && $global_config['MTHSMR']['ratio']=='scale') {
                    //     $mthsmr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='MTHSMR'&& $value->found_val) && $sumMTHSMR += $mthsmr;
                    ($value->found_ind=='MTHSMR'&& $value->found_divisor) && $sumMTHSMR += floatval($value->found_divisor);
                    ($value->found_ind=='MTHSMR'&& $value->found_divider) && $mthsmr += floatval($value->found_divider);
                    //-----MTHSMR
                    $index++;
                }
                if (!$res) {
                    break;
                }
                $count = count($res);
                // Log::info('mtwelveYearCon sumMTPSTR'.$sumMTPSTR);
                // Log::info('mtwelveYearCon count'.$count);
                // Log::info($sheetData);
                //合计
                $res['合计'][0] = '';
                $res['合计'][1] = '合计';
                // $res['合计'][2] = $global_config['MTPSTR']['ratio']=='default'? round( ($sumMTPSTR/$count), 3).$global_config['MTPSTR']['unit']:($global_config['MTPSTR']['ratio']=='percent'?round( ($sumMTPSTR/$count), 3).'%':round( ($sumMTPSTR/$count), 3).':1');
                $res['合计'][2] = ($mtpstr == 0 || $sumMTPSTR==0  ) ? '0' :  ($global_config['MTPSTR']['ratio']=='default'? round( ($sumMTPSTR/$mtpstr), 3).$global_config['MTPSTR']['unit']:($global_config['MTPSTR']['ratio']=='percent'?round( ($sumMTPSTR/$mtpstr)*100, 3).'%':round( ($sumMTPSTR/$mtpstr), 3).':1') );
                $res['合计'][3] = $global_config['MTPSTR']['standard_val'];
                $res['合计'][4] = $sumMTPSTR;
                $res['合计'][5] = $mtpstr;
                $res['合计'][6] = '合计';

                // $res['合计'][7] = $global_config['MTPTR']['ratio']=='default'? round( ($sumMTPTR/$count), 3).$global_config['MTPTR']['unit']:($global_config['MTPTR']['ratio']=='percent'?round( ($sumMTPTR/$count), 3).'%':round( ($sumMTPTR/$count), 3).':1');
                $res['合计'][7] =  ($mtptr == 0 || $sumMTPTR==0  ) ? '0' :  ($global_config['MTPTR']['ratio']=='default'? round( ($sumMTPTR/$mtptr), 3).$global_config['MTPTR']['unit']:($global_config['MTPTR']['ratio']=='percent'?round( ($sumMTPTR/$mtptr)*100, 3).'%':round( ($sumMTPTR/$mtptr), 3).':1') );
                $res['合计'][8] = $global_config['MTPTR']['standard_val'];
                $res['合计'][9] = $sumMTPTR;
                $res['合计'][10] = $mtptr;
                $res['合计'][11] = '合计';

                // $res['合计'][12] = $global_config['MTPFCR']['ratio']=='default'? round( ($sumMTPFCR/$count), 3).$global_config['MTPFCR']['unit']:($global_config['MTPFCR']['ratio']=='percent'?round( ($sumMTPFCR/$count), 3).'%':round( ($sumMTPFCR/$count), 3).':1');
                $res['合计'][12] = ($mtpfcr == 0 || $sumMTPFCR==0  ) ? '0' :  ($global_config['MTPFCR']['ratio']=='default'? round( ($sumMTPFCR/$mtpfcr), 3).$global_config['MTPFCR']['unit']:($global_config['MTPFCR']['ratio']=='percent'?round( ($sumMTPFCR/$mtpfcr)*100, 3).'%':round( ($sumMTPFCR/$mtpfcr), 3).':1') );
                $res['合计'][13] = $global_config['MTPFCR']['standard_val'];
                $res['合计'][14] = $sumMTPFCR;
                $res['合计'][15] = $mtpfcr;
                $res['合计'][16] = '合计';

                // $res['合计'][17] = $global_config['MTPHSTR']['ratio']=='default'? round( ($sumMTPHSTR/$count), 3).$global_config['MTPHSTR']['unit']:($global_config['MTPHSTR']['ratio']=='percent'?round( ($sumMTPHSTR/$count), 3).'%':round( ($sumMTPHSTR/$count), 3).':1');
                $res['合计'][17] = ($mtphstr == 0 || $sumMTPHSTR==0  ) ? '0' :  ($global_config['MTPHSTR']['ratio']=='default'? round( ($sumMTPHSTR/$mtphstr), 3).$global_config['MTPHSTR']['unit']:($global_config['MTPHSTR']['ratio']=='percent'?round( ($sumMTPHSTR/$mtphstr)*100, 3).'%':round( ($sumMTPHSTR/$mtphstr), 3).':1') );
                $res['合计'][18] = $global_config['MTPHSTR']['standard_val'];
                $res['合计'][19] = $sumMTPHSTR;
                $res['合计'][20] = $mtphstr;
                $res['合计'][21] = '合计';

                // $res['合计'][22] = $global_config['MTPSBR']['ratio']=='default'? round( ($sumMTPSBR/$count), 3).$global_config['MTPSBR']['unit']:($global_config['MTPSBR']['ratio']=='percent'?round( ($sumMTPSBR/$count), 3).'%':round( ($sumMTPSBR/$count), 3).':1');
                $res['合计'][22] = ($mtpsbr == 0 || $sumMTPSBR==0  ) ? '0' :   ($global_config['MTPSBR']['ratio']=='default'? round( ($sumMTPSBR/$mtpsbr), 3).$global_config['MTPSBR']['unit']:($global_config['MTPSBR']['ratio']=='percent'?round( ($sumMTPSBR/$mtpsbr)*100, 3).'%':round( ($sumMTPSBR/$mtpsbr), 3).':1') );
                $res['合计'][23] = $global_config['MTPSBR']['standard_val'];
                $res['合计'][24] = $sumMTPSBR;
                $res['合计'][25] = $mtpsbr;
                $res['合计'][26] = '合计';
                // $res['合计'][27] = $global_config['MTJSTR']['ratio']=='default'? round( ($sumMTJSTR/$count), 3).$global_config['MTJSTR']['unit']:($global_config['MTJSTR']['ratio']=='percent'?round( ($sumMTJSTR/$count), 3).'%':round( ($sumMTJSTR/$count), 3).':1');
                $res['合计'][27] =($mtjstr == 0 || $sumMTJSTR==0  ) ? '0' :   ($global_config['MTJSTR']['ratio']=='default'? round( ($sumMTJSTR/$mtjstr), 3).$global_config['MTJSTR']['unit']:($global_config['MTJSTR']['ratio']=='percent'?round( ($sumMTJSTR/$mtjstr)*100, 3).'%':round( ($sumMTJSTR/$mtjstr), 3).':1') );
                $res['合计'][28] = $global_config['MTJSTR']['standard_val'];
                $res['合计'][29] = $sumMTJSTR;
                $res['合计'][30] = $mtjstr;
                $res['合计'][31] = '合计';

                // $res['合计'][32] = $global_config['MTJETR']['ratio']=='default'? round( ($sumMTJETR/$count), 3).$global_config['MTJETR']['unit']:($global_config['MTJETR']['ratio']=='percent'?round( ($sumMTJETR/$count), 3).'%':round( ($sumMTJETR/$count), 3).':1');
                $res['合计'][32] = ($mtjetr == 0 || $sumMTJETR==0  ) ? '0' :  ($global_config['MTJETR']['ratio']=='default'? round( ($sumMTJETR/$mtjetr), 3).$global_config['MTJETR']['unit']:($global_config['MTJETR']['ratio']=='percent'?round( ($sumMTJETR/$mtjetr)*100, 3).'%':round( ($sumMTJETR/$mtjetr), 3).':1') );
                $res['合计'][33] = $global_config['MTJETR']['standard_val'];
                $res['合计'][34] = $sumMTJETR;
                $res['合计'][35] = $mtjetr;
                $res['合计'][36] = '合计';

                // $res['合计'][37] = $global_config['MTJFCR']['ratio']=='default'? round( ($sumMTJFCR/$count), 3).$global_config['MTJFCR']['unit']:($global_config['MTJFCR']['ratio']=='percent'?round( ($sumMTJFCR/$count), 3).'%':round( ($sumMTJFCR/$count), 3).':1');
                $res['合计'][37] = ($mtjfcr == 0 || $sumMTJFCR==0  ) ? '0' :  ($global_config['MTJFCR']['ratio']=='default'? round( ($sumMTJFCR/$mtjfcr), 3).$global_config['MTJFCR']['unit']:($global_config['MTJFCR']['ratio']=='percent'?round( ($sumMTJFCR/$mtjfcr)*100, 3).'%':round( ($sumMTJFCR/$mtjfcr), 3).':1') );
                $res['合计'][38] = $global_config['MTJFCR']['standard_val'];
                $res['合计'][39] = $sumMTJFCR;
                $res['合计'][40] = $mtjfcr;
                $res['合计'][41] = '合计';

                // $res['合计'][42] = $global_config['MTJHSTR']['ratio']=='default'? round( ($sumMTJHSTR/$count), 3).$global_config['MTJHSTR']['unit']:($global_config['MTJHSTR']['ratio']=='percent'?round( ($sumMTJHSTR/$count), 3).'%':round( ($sumMTJHSTR/$count), 3).':1');
                $res['合计'][42] = ($mtjhstr == 0 || $sumMTJHSTR==0  ) ? '0' :  ($global_config['MTJHSTR']['ratio']=='default'? round( ($sumMTJHSTR/$mtjhstr), 3).$global_config['MTJHSTR']['unit']:($global_config['MTJHSTR']['ratio']=='percent'?round( ($sumMTJHSTR/$mtjhstr)*100, 3).'%':round( ($sumMTJHSTR/$mtjhstr), 3).':1') );
                $res['合计'][43] = $global_config['MTJHSTR']['standard_val'];
                $res['合计'][44] = $sumMTJHSTR;
                $res['合计'][45] = $mtjhstr;
                $res['合计'][46] = '合计';

                // $res['合计'][47] = $global_config['MTJSBR']['ratio']=='default'? round( ($sumMTJSBR/$count), 3).$global_config['MTJSBR']['unit']:($global_config['MTJSBR']['ratio']=='percent'?round( ($sumMTJSBR/$count), 3).'%':round( ($sumMTJSBR/$count), 3).':1');
                $res['合计'][47] = ($mtjsbr == 0 || $sumMTJSBR==0  ) ? '0' :  ($global_config['MTJSBR']['ratio']=='default'? round( ($sumMTJSBR/$mtjsbr), 3).$global_config['MTJSBR']['unit']:($global_config['MTJSBR']['ratio']=='percent'?round( ($sumMTJSBR/$mtjsbr)*100, 3).'%':round( ($sumMTJSBR/$mtjsbr), 3).':1') );
                $res['合计'][48] = $global_config['MTJSBR']['standard_val'];
                $res['合计'][49] = $sumMTJSBR;
                $res['合计'][50] = $mtjsbr;
                $res['合计'][51] = '合计';

                // $res['合计'][52] = $global_config['MTHSTR']['ratio']=='default'? round( ($sumMTHSTR/$count), 3).$global_config['MTHSTR']['unit']:($global_config['MTHSTR']['ratio']=='percent'?round( ($sumMTHSTR/$count), 3).'%':round( ($sumMTHSTR/$count), 3).':1');
                $res['合计'][52] =  ($mthstr == 0 || $sumMTHSTR==0  ) ? '0' :  ($global_config['MTHSTR']['ratio']=='default'? round( ($sumMTHSTR/$mthstr), 3).$global_config['MTHSTR']['unit']:($global_config['MTHSTR']['ratio']=='percent'?round( ($sumMTHSTR/$mthstr)*100, 3).'%':round( ($sumMTHSTR/$mthstr), 3).':1') );
                $res['合计'][53] = $global_config['MTHSTR']['standard_val'];
                $res['合计'][54] = $sumMTHSTR;
                $res['合计'][55] = $mthstr;
                $res['合计'][56] = '合计';

                // $res['合计'][57] = $global_config['MTHETR']['ratio']=='default'? round( ($sumMTHETR/$count), 3).$global_config['MTHETR']['unit']:($global_config['MTHETR']['ratio']=='percent'?round( ($sumMTHETR/$count), 3).'%':round( ($sumMTHETR/$count), 3).':1');
                $res['合计'][57] = ($mthetr == 0 || $sumMTHETR==0  ) ? '0' :  ($global_config['MTHETR']['ratio']=='default'? round( ($sumMTHETR/$mthetr), 3).$global_config['MTHETR']['unit']:($global_config['MTHETR']['ratio']=='percent'?round( ($sumMTHETR/$mthetr)*100, 3).'%':round( ($sumMTHETR/$mthetr), 3).':1') );
                $res['合计'][58] = $global_config['MTHETR']['standard_val'];
                $res['合计'][59] = $sumMTHETR;
                $res['合计'][60] = $mthetr;
                $res['合计'][61] = '合计';

                // $res['合计'][62] = $global_config['MTHSMR']['ratio']=='default'? round( ($sumMTHSMR/$count), 3).$global_config['MTHSMR']['unit']:($global_config['MTHSMR']['ratio']=='percent'?round( ($sumMTHSMR/$count), 3).'%':round( ($sumMTHSMR/$count), 3).':1');
                $res['合计'][62] = ($mthsmr == 0 || $sumMTHSMR==0  ) ? '0' :  ($global_config['MTHSMR']['ratio']=='default'? round( ($sumMTHSMR/$mthsmr), 3).$global_config['MTHSMR']['unit']:($global_config['MTHSMR']['ratio']=='percent'?round( ($sumMTHSMR/$mthsmr)*100, 3).'%':round( ($sumMTHSMR/$mthsmr), 3).':1') );
                $res['合计'][63] = $global_config['MTHSMR']['standard_val'];
                $res['合计'][64] = $sumMTHSMR;
                $res['合计'][65] = $mthsmr;
                $res['合计'][66] = '合计';

                // Log::info('mtwelveYearCon res');
                // Log::info($res);

                break;
            case 'highSchool':
                $res = [];
                $index = 1;
                $sumHSTR = 0;
                $sumHETR = 0;
                $sumHSMR = 0;
                $hstr = 0;
                $hetr = 0;
                $hsmr = 0;
                foreach ($sheetData as $value) {
                    if (in_array($value->user_id.$value->school.$value->school_type.$value->found_ind, $repeat)) {
                        continue;
                    }
                    //并发重复数据处理
                    $repeat[] = $value->user_id.$value->school.$value->school_type.$value->found_ind;
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='HSTR' && $res[$value->school][2] = $value->found_ind=='HSTR' ? $value->found_val:'';
                    $value->found_ind=='HSTR' && $res[$value->school][3] = $value->found_ind=='HSTR' ? $value->standard_val:'';
                    $value->found_ind=='HSTR' && $res[$value->school][4] = $value->found_ind=='HSTR' ? $value->found_divisor:'';
                    $value->found_ind=='HSTR' && $res[$value->school][5] = $value->found_ind=='HSTR' ? $value->found_divider:'';
                    $value->found_ind=='HSTR' && $res[$value->school][6] = $value->found_ind=='HSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    $value->found_ind=='HETR' && $res[$value->school][7] = $value->found_ind=='HETR' ? $value->found_val:'';
                    $value->found_ind=='HETR' && $res[$value->school][8] = $value->found_ind=='HETR' ? $value->standard_val:'';
                    $value->found_ind=='HETR' && $res[$value->school][9] = $value->found_ind=='HETR' ? $value->found_divisor:'';
                    $value->found_ind=='HETR' && $res[$value->school][10] = $value->found_ind=='HETR' ? $value->found_divider:'';
                    $value->found_ind=='HETR' && $res[$value->school][11] = $value->found_ind=='HETR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='HSMR' && $res[$value->school][12] = $value->found_ind=='HSMR' ? $value->found_val:'';
                    $value->found_ind=='HSMR' && $res[$value->school][13] = $value->found_ind=='HSMR' ? $value->standard_val:'';
                    $value->found_ind=='HSMR' && $res[$value->school][14] = $value->found_ind=='HSMR' ? $value->found_divisor:'';
                    $value->found_ind=='HSMR' && $res[$value->school][15] = $value->found_ind=='HSMR' ? $value->found_divider:'';
                    $value->found_ind=='HSMR' && $res[$value->school][16] = $value->found_ind=='HSMR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    //------HSTR
                    // if ($value->found_ind=='HSTR' && ($global_config['HSTR']['ratio']=='default' || $global_config['HSTR']['ratio']=='percent')) {
                    //     $hstr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='HSTR' && $global_config['HSTR']['ratio']=='scale') {
                    //     $hstr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='HSTR'&& $value->found_val) && $sumHSTR += $hstr;
                    ($value->found_ind=='HSTR'&& $value->found_divisor) && $sumHSTR += floatval($value->found_divisor);
                    ($value->found_ind=='HSTR'&& $value->found_divider) && $hstr += floatval($value->found_divider);
                    //---HSTR
                    //
                    //HETR
                    // if ($value->found_ind=='HETR' && ($global_config['HETR']['ratio']=='default' || $global_config['HETR']['ratio']=='percent')) {
                    //     $hetr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='HETR' && $global_config['HETR']['ratio']=='scale') {
                    //     $hetr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='HETR'&& $value->found_val) && $sumHETR += $hetr;
                    ($value->found_ind=='HETR'&& $value->found_divisor) && $sumHETR += floatval($value->found_divisor);
                    ($value->found_ind=='HETR'&& $value->found_divider) && $hetr += floatval($value->found_divider);
                    //---HETR
                    
                    //---HSMR
                    // if ($value->found_ind=='HSMR' && ($global_config['HSMR']['ratio']=='default' || $global_config['HSMR']['ratio']=='percent')) {
                    //     $hsmr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='HSMR' && $global_config['HSMR']['ratio']=='scale') {
                    //     $hsmr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='HSMR'&& $value->found_val) && $sumHSMR += $hsmr;
                    ($value->found_ind=='HSMR'&& $value->found_divisor) && $sumHSMR += floatval($value->found_divisor);
                    ($value->found_ind=='HSMR'&& $value->found_divider) && $hsmr += floatval($value->found_divider);
                    //-----HSMR
                    $index++;
                }

                if (!$res) {
                    break;
                }
                $count = count($res);
                $res['合计'][0] = '';
                $res['合计'][1] = '合计';
                // $res['合计'][2] = $global_config['HSTR']['ratio']=='default'? round( ($sumHSTR/$count), 3).$global_config['HSTR']['unit']:($global_config['HSTR']['ratio']=='percent'?round( ($sumHSTR/$count), 3).'%':round( ($sumHSTR/$count), 3).':1');
                $res['合计'][2] = ($hstr == 0 || $sumHSTR==0  ) ? '0' :  ($global_config['HSTR']['ratio']=='default'? round( ($sumHSTR/$hstr), 3).$global_config['HSTR']['unit']:($global_config['HSTR']['ratio']=='percent'?round( ($sumHSTR/$hstr)*100, 3).'%':round( ($sumHSTR/$hstr), 3).':1') );
                $res['合计'][3] = $global_config['HSTR']['standard_val'];
                $res['合计'][4] = $sumHSTR;
                $res['合计'][5] = $hstr;
                $res['合计'][6] = '合计';

                // $res['合计'][7] = $global_config['HETR']['ratio']=='default'? round( ($sumHETR/$count), 3).$global_config['HETR']['unit']:($global_config['HETR']['ratio']=='percent'?round( ($sumHETR/$count), 3).'%':round( ($sumHETR/$count), 3).':1');
                $res['合计'][7] = ($hetr == 0 || $sumHETR==0  ) ? '0' :  ($global_config['HETR']['ratio']=='default'? round( ($sumHETR/$hetr), 3).$global_config['HETR']['unit']:($global_config['HETR']['ratio']=='percent'?round( ($sumHETR/$hetr)*100, 3).'%':round( ($sumHETR/$hetr), 3).':1') );
                $res['合计'][8] = $global_config['HETR']['standard_val'];
                $res['合计'][9] = $sumHETR;
                $res['合计'][10] = $hetr;
                $res['合计'][11] = '合计';

                // $res['合计'][12] = $global_config['HSMR']['ratio']=='default'? round( ($sumHSMR/$count), 3).$global_config['HSMR']['unit']:($global_config['HSMR']['ratio']=='percent'?round( ($sumHSMR/$count), 3).'%':round( ($sumHSMR/$count), 3).':1');
                $res['合计'][12] = ($hsmr == 0 || $sumHSMR==0  ) ? '0' : ($global_config['HSMR']['ratio']=='default'? round( ($sumHSMR/$hsmr), 3).$global_config['HSMR']['unit']:($global_config['HSMR']['ratio']=='percent'?round( ($sumHSMR/$hsmr)*100, 3).'%':round( ($sumHSMR/$hsmr), 3).':1') );
                $res['合计'][13] = $global_config['HSMR']['standard_val'];
                $res['合计'][14] = $sumHSMR;
                $res['合计'][15] = $hsmr;
                $res['合计'][16] = '合计';

                break;
            case 'secondaryVocationalSchool':
                $res = [];
                $index = 1;
                $sumVETR = 0;
                $sumVSTR = 0;
                $sumVSMR = 0;
                $vstr = 0;
                $vetr = 0;
                $vsmr = 0;
                foreach ($sheetData as $value) {
                    if (in_array($value->user_id.$value->school.$value->school_type.$value->found_ind, $repeat)) {
                        continue;
                    }
                    //并发重复数据处理
                    $repeat[] = $value->user_id.$value->school.$value->school_type.$value->found_ind;
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;

                    $value->found_ind=='VSTR' && $res[$value->school][2] = $value->found_ind=='VSTR' ? $value->found_val:'';
                    $value->found_ind=='VSTR' && $res[$value->school][3] = $value->found_ind=='VSTR' ? $value->standard_val:'';
                    $value->found_ind=='VSTR' && $res[$value->school][4] = $value->found_ind=='VSTR' ? $value->found_divisor:'';
                    $value->found_ind=='VSTR' && $res[$value->school][5] = $value->found_ind=='VSTR' ? $value->found_divider:'';
                    $value->found_ind=='VSTR' && $res[$value->school][6] = $value->found_ind=='VSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='VETR' && $res[$value->school][7] = $value->found_ind=='VETR' ? $value->found_val:'';
                    $value->found_ind=='VETR' && $res[$value->school][8] = $value->found_ind=='VETR' ? $value->standard_val:'';
                    $value->found_ind=='VETR' && $res[$value->school][9] = $value->found_ind=='VETR' ? $value->found_divisor:'';
                    $value->found_ind=='VETR' && $res[$value->school][10] = $value->found_ind=='VETR' ? $value->found_divider:'';
                    $value->found_ind=='VETR' && $res[$value->school][11] = $value->found_ind=='VETR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='VSMR' && $res[$value->school][12] = $value->found_ind=='VSMR' ? $value->found_val:'';
                    $value->found_ind=='VSMR' && $res[$value->school][13] = $value->found_ind=='VSMR' ? $value->standard_val:'';
                    $value->found_ind=='VSMR' && $res[$value->school][14] = $value->found_ind=='VSMR' ? $value->found_divisor:'';
                    $value->found_ind=='VSMR' && $res[$value->school][15] = $value->found_ind=='VSMR' ? $value->found_divider:'';
                    $value->found_ind=='VSMR' && $res[$value->school][16] = $value->found_ind=='VSMR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    //------VSTR
                    // if ($value->found_ind=='VSTR' && ($global_config['VSTR']['ratio']=='default' || $global_config['VSTR']['ratio']=='percent')) {
                    //     $vstr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='VSTR' && $global_config['VSTR']['ratio']=='scale') {
                    //     $vstr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='VSTR'&& $value->found_val) && $sumVSTR += $vstr;
                    ($value->found_ind=='VSTR'&& $value->found_divisor) && $sumVSTR += floatval($value->found_divisor);
                    ($value->found_ind=='VSTR'&& $value->found_divider) && $vstr += floatval($value->found_divider);
                    //---VSTR
                    //
                    //VETR
                    // if ($value->found_ind=='VETR' && ($global_config['VETR']['ratio']=='default' || $global_config['VETR']['ratio']=='percent')) {
                    //     $vetr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='VETR' && $global_config['VETR']['ratio']=='scale') {
                    //     $vetr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='VETR'&& $value->found_val) && $sumVETR += $vetr;
                    ($value->found_ind=='VETR'&& $value->found_divisor) && $sumVETR += floatval($value->found_divisor);
                    ($value->found_ind=='VETR'&& $value->found_divider) && $vetr += floatval($value->found_divider);
                    //---VETR
                    
                    //---VSMR
                    // if ($value->found_ind=='VSMR' && ($global_config['VSMR']['ratio']=='default' || $global_config['VSMR']['ratio']=='percent')) {
                    //     $vsmr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='VSMR' && $global_config['VSMR']['ratio']=='scale') {
                    //     $vsmr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='VSMR'&& $value->found_val) && $sumVSMR += $vsmr;
                    ($value->found_ind=='VSMR'&& $value->found_divisor) && $sumVSMR += floatval($value->found_divisor);
                    ($value->found_ind=='VSMR'&& $value->found_divider) && $vsmr += floatval($value->found_divider);
                    //-----VSMR
                    $index++;
                }

                if (!$res) {
                    break;
                }
                $count = count($res);
                $res['合计'][0] = '';
                $res['合计'][1] = '合计';
                // $res['合计'][2] = $global_config['VSTR']['ratio']=='default'? round( ($sumVSTR/$count), 3).$global_config['VSTR']['unit']:($global_config['VSTR']['ratio']=='percent'?round( ($sumVSTR/$count), 3).'%':round( ($sumVSTR/$count), 3).':1');
                $res['合计'][2] = ($vstr == 0 || $sumVSTR==0  ) ? '0' : ( $global_config['VSTR']['ratio']=='default'? round( ($sumVSTR/$vstr), 3).$global_config['VSTR']['unit']:($global_config['VSTR']['ratio']=='percent'?round( ($sumVSTR/$vstr)*100, 3).'%':round( ($sumVSTR/$vstr), 3).':1') );
                $res['合计'][3] = $global_config['VSTR']['standard_val'];
                $res['合计'][4] = $sumVSTR;
                $res['合计'][5] = $vstr;
                $res['合计'][6] = '合计';

                // $res['合计'][7] = $global_config['VETR']['ratio']=='default'? round( ($sumVETR/$count), 3).$global_config['VETR']['unit']:($global_config['VETR']['ratio']=='percent'?round( ($sumVETR/$count), 3).'%':round( ($sumVETR/$count), 3).':1');
                $res['合计'][7] = ($vetr == 0 || $sumVETR==0  ) ? '0' : ( $global_config['VETR']['ratio']=='default'? round( ($sumVETR/$vetr), 3).$global_config['VETR']['unit']:($global_config['VETR']['ratio']=='percent'?round( ($sumVETR/$vetr)*100, 3).'%':round( ($sumVETR/$vetr), 3).':1') );
                $res['合计'][8] = $global_config['VETR']['standard_val'];
                $res['合计'][9] = $sumVETR;
                $res['合计'][10] = $vetr;
                $res['合计'][11] = '合计';

                // $res['合计'][12] = $global_config['VSMR']['ratio']=='default'? round( ($sumVSMR/$count), 3).$global_config['VSMR']['unit']:($global_config['VSMR']['ratio']=='percent'?round( ($sumVSMR/$count), 3).'%':round( ($sumVSMR/$count), 3).':1');
                Log::info('secondaryVocationalSchool $vsmr'.$vsmr.'|sumVSMR'.$sumVSMR);
                $res['合计'][12] = ($vsmr == 0 || $sumVSMR==0  ) ? '0' : ( $global_config['VSMR']['ratio']=='default'? round( ($sumVSMR/$vsmr), 3).$global_config['VSMR']['unit']:($global_config['VSMR']['ratio']=='percent'?round( ($sumVSMR/$vsmr)*100, 3).'%':round( ($sumVSMR/$vsmr), 3).':1') );
                $res['合计'][13] = $global_config['VSMR']['standard_val'];
                $res['合计'][14] = $sumVSMR;
                $res['合计'][15] = $vsmr;
                $res['合计'][16] = '合计';
                break;    
            case 'specialSchool':
                $res = [];
                $index = 1;
                $sumSSTR = 0;
                $sstr = 0;
                foreach ($sheetData as $value) {
                    if (in_array($value->user_id.$value->school.$value->school_type.$value->found_ind, $repeat)) {
                        continue;
                    }
                    //并发重复数据处理
                    $repeat[] = $value->user_id.$value->school.$value->school_type.$value->found_ind;
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='SSTR' && $res[$value->school][2] = $value->found_ind=='SSTR' ? $value->found_val:'';
                    $value->found_ind=='SSTR' && $res[$value->school][3] = $value->found_ind=='SSTR' ? $value->standard_val:'';
                    $value->found_ind=='SSTR' && $res[$value->school][4] = $value->found_ind=='SSTR' ? $value->found_divisor:'';
                    $value->found_ind=='SSTR' && $res[$value->school][5] = $value->found_ind=='SSTR' ? $value->found_divider:'';
                    $value->found_ind=='SSTR' && $res[$value->school][6] = $value->found_ind=='SSTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    //---SSTR
                    // if ($value->found_ind=='SSTR' && ($global_config['SSTR']['ratio']=='default' || $global_config['SSTR']['ratio']=='percent')) {
                    //     $sstr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='SSTR' && $global_config['SSTR']['ratio']=='scale') {
                    //     $sstr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='SSTR'&& $value->found_val) && $sumSSTR += $sstr;
                    ($value->found_ind=='SSTR'&& $value->found_divisor) && $sumSSTR += floatval($value->found_divisor);
                    ($value->found_ind=='SSTR'&& $value->found_divider) && $sstr += floatval($value->found_divider);
                    //-----SSTR
                    $index++;
                }
                if (!$res) {
                    break;
                }
                $count = count($res);
                $res['合计'][0] = '';
                $res['合计'][1] = '合计';
                // $res['合计'][2] = $global_config['SSTR']['ratio']=='default'? round( ($sumSSTR/$count), 3).$global_config['SSTR']['unit']:($global_config['SSTR']['ratio']=='percent'?round( ($sumSSTR/$count), 3).'%':round( ($sumSSTR/$count), 3).':1');
                $res['合计'][2] = ($sstr == 0 || $sumSSTR==0  ) ? '0' : ($global_config['SSTR']['ratio']=='default'? round( ($sumSSTR/$sstr), 3).$global_config['SSTR']['unit']:($global_config['SSTR']['ratio']=='percent'?round( ($sumSSTR/$sstr)*100, 3).'%':round( ($sumSSTR/$sstr), 3).':1') );
                $res['合计'][3] = $global_config['SSTR']['standard_val'];
                $res['合计'][4] = $sumSSTR;
                $res['合计'][5] = $sstr;
                $res['合计'][6] = '合计';
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
        // Log::info('__getModernQuery');
        // Log::info($res);
        return $res;
    }

    private function __getBalanceQuery($school_type, $sheetData)
    {
        $res = [];
        $repeat = [];
        $global_config = config('ixport.SCHOOL_IMPORT_FOUND_INDEX');
        switch ($school_type) {
            case 'primarySchool':
                $res = [];
                $index = 1;
                $sumPHETR = 0;
                $sumPHBTR = 0;
                $sumPHATR = 0;
                $sumPSRAR = 0;
                $sumPSMAR = 0;
                $sumPSMR = 0;
                $sumPHIR = 0;
                $phetr = 0;
                $phbtr = 0;
                $phatr = 0;
                $psrar = 0;
                $psmar = 0;
                $psmr = 0;
                $phir = 0;
                foreach ($sheetData as $value) {
                    if (in_array($value->user_id.$value->school.$value->school_type.$value->found_ind, $repeat)) {
                        continue;
                    }
                    //并发重复数据处理
                    $repeat[] = $value->user_id.$value->school.$value->school_type.$value->found_ind;
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='PHETR' && $res[$value->school][2] = $value->found_ind=='PHETR' ? $value->basic_val:'';
                    $value->found_ind=='PHETR' && $res[$value->school][3] = $value->found_ind=='PHETR' ? $value->standard_val:'';
                    $value->found_ind=='PHETR' && $res[$value->school][4] = $value->found_ind=='PHETR' ? $value->found_val:'';
                    $value->found_ind=='PHETR' && $res[$value->school][5] = $value->found_ind=='PHETR' ? $value->found_divisor:'';
                    $value->found_ind=='PHETR' && $res[$value->school][6] = $value->found_ind=='PHETR' ? $value->found_divider:'';
                    $value->found_ind=='PHETR' && $res[$value->school][7] = $value->found_ind=='PHETR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PHBTR' && $res[$value->school][8] = $value->found_ind=='PHBTR' ? $value->basic_val:'';
                    $value->found_ind=='PHBTR' && $res[$value->school][9] = $value->found_ind=='PHBTR' ? $value->standard_val:'';
                    $value->found_ind=='PHBTR' && $res[$value->school][10] = $value->found_ind=='PHBTR' ? $value->found_val:'';
                    $value->found_ind=='PHBTR' && $res[$value->school][11] = $value->found_ind=='PHBTR' ? $value->found_divisor:'';
                    $value->found_ind=='PHBTR' && $res[$value->school][12] = $value->found_ind=='PHBTR' ? $value->found_divider:'';
                    $value->found_ind=='PHBTR' && $res[$value->school][13] = $value->found_ind=='PHBTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PHATR' && $res[$value->school][14] = $value->found_ind=='PHATR' ? $value->basic_val:'';
                    $value->found_ind=='PHATR' && $res[$value->school][15] = $value->found_ind=='PHATR' ? $value->standard_val:'';
                    $value->found_ind=='PHATR' && $res[$value->school][16] = $value->found_ind=='PHATR' ? $value->found_val:'';
                    $value->found_ind=='PHATR' && $res[$value->school][17] = $value->found_ind=='PHATR' ? $value->found_divisor:'';
                    $value->found_ind=='PHATR' && $res[$value->school][18] = $value->found_ind=='PHATR' ? $value->found_divider:'';
                    $value->found_ind=='PHATR' && $res[$value->school][19] = $value->found_ind=='PHATR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PSRAR' && $res[$value->school][20] = $value->found_ind=='PSRAR' ? $value->basic_val:'';
                    $value->found_ind=='PSRAR' && $res[$value->school][21] = $value->found_ind=='PSRAR' ? $value->standard_val:'';
                    $value->found_ind=='PSRAR' && $res[$value->school][22] = $value->found_ind=='PSRAR' ? $value->found_val:'';
                    $value->found_ind=='PSRAR' && $res[$value->school][23] = $value->found_ind=='PSRAR' ? $value->found_divisor:'';
                    $value->found_ind=='PSRAR' && $res[$value->school][24] = $value->found_ind=='PSRAR' ? $value->found_divider:'';
                    $value->found_ind=='PSRAR' && $res[$value->school][25] = $value->found_ind=='PSRAR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PSMAR' && $res[$value->school][26] = $value->found_ind=='PSMAR' ? $value->basic_val:'';
                    $value->found_ind=='PSMAR' && $res[$value->school][27] = $value->found_ind=='PSMAR' ? $value->standard_val:'';
                    $value->found_ind=='PSMAR' && $res[$value->school][28] = $value->found_ind=='PSMAR' ? $value->found_val:'';
                    $value->found_ind=='PSMAR' && $res[$value->school][29] = $value->found_ind=='PSMAR' ? $value->found_divisor:'';
                    $value->found_ind=='PSMAR' && $res[$value->school][30] = $value->found_ind=='PSMAR' ? $value->found_divider:'';
                    $value->found_ind=='PSMAR' && $res[$value->school][31] = $value->found_ind=='PSMAR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PSMR' && $res[$value->school][32] = $value->found_ind=='PSMR' ? $value->basic_val:'';
                    $value->found_ind=='PSMR' && $res[$value->school][33] = $value->found_ind=='PSMR' ? $value->standard_val:'';
                    $value->found_ind=='PSMR' && $res[$value->school][34] = $value->found_ind=='PSMR' ? $value->found_val:'';
                    $value->found_ind=='PSMR' && $res[$value->school][35] = $value->found_ind=='PSMR' ? $value->found_divisor:'';
                    $value->found_ind=='PSMR' && $res[$value->school][36] = $value->found_ind=='PSMR' ? $value->found_divider:'';
                    $value->found_ind=='PSMR' && $res[$value->school][37] = $value->found_ind=='PSMR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='PHIR' && $res[$value->school][38] = $value->found_ind=='PHIR' ? $value->basic_val:'';
                    $value->found_ind=='PHIR' && $res[$value->school][39] = $value->found_ind=='PHIR' ? $value->standard_val:'';
                    $value->found_ind=='PHIR' && $res[$value->school][40] = $value->found_ind=='PHIR' ? $value->found_val:'';
                    $value->found_ind=='PHIR' && $res[$value->school][41] = $value->found_ind=='PHIR' ? $value->found_divisor:'';
                    $value->found_ind=='PHIR' && $res[$value->school][42] = $value->found_ind=='PHIR' ? $value->found_divider:'';
                    $value->found_ind=='PHIR' && $res[$value->school][43] = $value->found_ind=='PHIR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    
                    //---PHETR
                    // if ($value->found_ind=='PHETR' && ($global_config['PHETR']['ratio']=='default' || $global_config['PHETR']['ratio']=='percent')) {
                    //     $phetr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='PHETR' && $global_config['PHETR']['ratio']=='scale') {
                    //     $phetr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='PHETR'&& $value->found_val) && $sumPHETR += $phetr;
                    ($value->found_ind=='PHETR'&& $value->found_divisor) && $sumPHETR += floatval($value->found_divisor);
                    ($value->found_ind=='PHETR'&& $value->found_divider) && $phetr += floatval($value->found_divider);
                    //-----PHETR
                    //---PHBTR
                    // if ($value->found_ind=='PHBTR' && ($global_config['PHBTR']['ratio']=='default' || $global_config['PHBTR']['ratio']=='percent')) {
                    //     $phbtr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='PHBTR' && $global_config['PHBTR']['ratio']=='scale') {
                    //     $phbtr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='PHBTR'&& $value->found_val) && $sumPHBTR += $phbtr;
                    ($value->found_ind=='PHBTR'&& $value->found_divisor) && $sumPHBTR += floatval($value->found_divisor);
                    ($value->found_ind=='PHBTR'&& $value->found_divider) && $phbtr += floatval($value->found_divider);
                    //-----PHBTR
                    //---PHATR
                    // if ($value->found_ind=='PHATR' && ($global_config['PHATR']['ratio']=='default' || $global_config['PHATR']['ratio']=='percent')) {
                    //     $phatr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='PHATR' && $global_config['PHATR']['ratio']=='scale') {
                    //     $phatr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='PHATR'&& $value->found_val) && $sumPHATR += $phatr;
                    ($value->found_ind=='PHATR'&& $value->found_divisor) && $sumPHATR += floatval($value->found_divisor);
                    ($value->found_ind=='PHATR'&& $value->found_divider) && $phatr += floatval($value->found_divider);
                    //-----PHATR
                    //---PSRAR
                    // if ($value->found_ind=='PSRAR' && ($global_config['PSRAR']['ratio']=='default' || $global_config['PSRAR']['ratio']=='percent')) {
                    //     $psrar = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='PSRAR' && $global_config['PSRAR']['ratio']=='scale') {
                    //     $psrar = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='PSRAR'&& $value->found_val) && $sumPSRAR += $psrar;
                    ($value->found_ind=='PSRAR'&& $value->found_divisor) && $sumPSRAR += floatval($value->found_divisor);
                    ($value->found_ind=='PSRAR'&& $value->found_divider) && $psrar += floatval($value->found_divider);
                    //-----PSRAR
                    //---PSMAR
                    // if ($value->found_ind=='PSMAR' && ($global_config['PSMAR']['ratio']=='default' || $global_config['PSMAR']['ratio']=='percent')) {
                    //     $psmar = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='PSMAR' && $global_config['PSMAR']['ratio']=='scale') {
                    //     $psmar = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='PSMAR'&& $value->found_val) && $sumPSMAR += $psmar;
                    ($value->found_ind=='PSMAR'&& $value->found_divisor) && $sumPSMAR += floatval($value->found_divisor);
                    ($value->found_ind=='PSMAR'&& $value->found_divider) && $psmar += floatval($value->found_divider);
                    //-----PSMAR
                    //---PSMR
                    // if ($value->found_ind=='PSMR' && ($global_config['PSMR']['ratio']=='default' || $global_config['PSMR']['ratio']=='percent')) {
                    //     $psmr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='PSMR' && $global_config['PSMR']['ratio']=='scale') {
                    //     $psmr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='PSMR'&& $value->found_val) && $sumPSMR += $psmr;
                    ($value->found_ind=='PSMR'&& $value->found_divisor) && $sumPSMR += floatval($value->found_divisor);
                    ($value->found_ind=='PSMR'&& $value->found_divider) && $psmr += floatval($value->found_divider);
                    //-----PSMR
                    //---PHIR
                    // if ($value->found_ind=='PHIR' && ($global_config['PHIR']['ratio']=='default' || $global_config['PHIR']['ratio']=='percent')) {
                    //     $phir = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='PHIR' && $global_config['PHIR']['ratio']=='scale') {
                    //     $phir = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='PHIR'&& $value->found_val) && $sumPHIR += $phir;
                    ($value->found_ind=='PHIR'&& $value->found_divisor) && $sumPHIR += floatval($value->found_divisor);
                    ($value->found_ind=='PHIR'&& $value->found_divider) && $phir += floatval($value->found_divider);
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
                // $res['合计'][4] = $global_config['PHETR']['ratio']=='default'? round( ($sumPHETR/$count), 3).$global_config['PHETR']['unit']:($global_config['PHETR']['ratio']=='percent'?round( ($sumPHETR/$count), 3).'%':round( ($sumPHETR/$count), 3).':1');
                $res['合计'][4] = ($phetr == 0 || $sumPHETR==0  ) ? '0' :  ($global_config['PHETR']['ratio']=='default'? round( ($sumPHETR/$phetr), 3).$global_config['PHETR']['unit']:($global_config['PHETR']['ratio']=='percent'?round( ($sumPHETR/$phetr)*100, 3).'%':round( ($sumPHETR/$phetr), 3).':1') );
                $res['合计'][5] = $sumPHETR;
                $res['合计'][6] = $phetr;
                $res['合计'][7] = '合计';

                $res['合计'][8] = $global_config['PHBTR']['basic_val'];
                $res['合计'][9] = $global_config['PHBTR']['standard_val'];
                // $res['合计'][10] = $global_config['PHBTR']['ratio']=='default'? round( ($sumPHBTR/$count), 3).$global_config['PHBTR']['unit']:($global_config['PHBTR']['ratio']=='percent'?round( ($sumPHBTR/$count), 3).'%':round( ($sumPHBTR/$count), 3).':1');
                $res['合计'][10] = ($phbtr == 0 || $sumPHBTR==0  ) ? '0' :  ($global_config['PHBTR']['ratio']=='default'? round( ($sumPHBTR/$phbtr), 3).$global_config['PHBTR']['unit']:($global_config['PHBTR']['ratio']=='percent'?round( ($sumPHBTR/$phbtr)*100, 3).'%':round( ($sumPHBTR/$phbtr), 3).':1') );
                $res['合计'][11] = $sumPHBTR;
                $res['合计'][12] = $phbtr;
                $res['合计'][13] = '合计';

                $res['合计'][14] = $global_config['PHATR']['basic_val'];
                $res['合计'][15] = $global_config['PHATR']['standard_val'];
                // $res['合计'][16] = $global_config['PHATR']['ratio']=='default'? round( ($sumPHATR/$count), 3).$global_config['PHATR']['unit']:($global_config['PHATR']['ratio']=='percent'?round( ($sumPHATR/$count), 3).'%':round( ($sumPHATR/$count), 3).':1');
                $res['合计'][16] = ($phatr == 0 || $sumPHATR==0  ) ? '0' :  ($global_config['PHATR']['ratio']=='default'? round( ($sumPHATR/$phatr), 3).$global_config['PHATR']['unit']:($global_config['PHATR']['ratio']=='percent'?round( ($sumPHATR/$phatr)*100, 3).'%':round( ($sumPHATR/$phatr), 3).':1') );
                $res['合计'][17] = $sumPHATR;
                $res['合计'][18] = $phatr;
                $res['合计'][19] = '合计';

                $res['合计'][20] = $global_config['PSRAR']['basic_val'];
                $res['合计'][21] = $global_config['PSRAR']['standard_val'];
                // $res['合计'][22] = $global_config['PSRAR']['ratio']=='default'? round( ($sumPSRAR/$count), 3).$global_config['PSRAR']['unit']:($global_config['PSRAR']['ratio']=='percent'?round( ($sumPSRAR/$count), 3).'%':round( ($sumPSRAR/$count), 3).':1');
                $res['合计'][22] =  ($psrar == 0 || $sumPSRAR==0  ) ? '0' :  ($global_config['PSRAR']['ratio']=='default'? round( ($sumPSRAR/$psrar), 3).$global_config['PSRAR']['unit']:($global_config['PSRAR']['ratio']=='percent'?round( ($sumPSRAR/$psrar)*100, 3).'%':round( ($sumPSRAR/$psrar), 3).':1') );
                $res['合计'][23] = $sumPSRAR;
                $res['合计'][24] = $psrar;
                $res['合计'][25] = '合计';

                $res['合计'][26] = $global_config['PSMAR']['basic_val'];
                $res['合计'][27] = $global_config['PSMAR']['standard_val'];
                // $res['合计'][28] = $global_config['PSMAR']['ratio']=='default'? round( ($sumPSMAR/$count), 3).$global_config['PSMAR']['unit']:($global_config['PSMAR']['ratio']=='percent'?round( ($sumPSMAR/$count), 3).'%':round( ($sumPSMAR/$count), 3).':1');
                $res['合计'][28] = ($psmar == 0 || $sumPSMAR==0  ) ? '0' :  ($global_config['PSMAR']['ratio']=='default'? round( ($sumPSMAR/$psmar), 3).$global_config['PSMAR']['unit']:($global_config['PSMAR']['ratio']=='percent'?round( ($sumPSMAR/$psmar)*100, 3).'%':round( ($sumPSMAR/$psmar), 3).':1') );
                $res['合计'][29] = $sumPSMAR;
                $res['合计'][30] = $psmar;
                $res['合计'][31] = '合计';

                $res['合计'][32] = $global_config['PSMR']['basic_val'];
                $res['合计'][33] = $global_config['PSMR']['standard_val'];
                // $res['合计'][34] = $global_config['PSMR']['ratio']=='default'? round( ($sumPSMR/$count), 3).$global_config['PSMR']['unit']:($global_config['PSMR']['ratio']=='percent'?round( ($sumPSMR/$count), 3).'%':round( ($sumPSMR/$count), 3).':1');
                $res['合计'][34] = ($psmr == 0 || $sumPSMR==0  ) ? '0' :  ($global_config['PSMR']['ratio']=='default'? round( ($sumPSMR/$psmr), 3).$global_config['PSMR']['unit']:($global_config['PSMR']['ratio']=='percent'?round( ($sumPSMR/$psmr)*100, 3).'%':round( ($sumPSMR/$psmr), 3).':1') );
                $res['合计'][35] = $sumPSMR;
                $res['合计'][36] = $psmr;
                $res['合计'][37] = '合计';

                $res['合计'][38] = $global_config['PHIR']['basic_val'];
                $res['合计'][39] = $global_config['PHIR']['standard_val'];
                // $res['合计'][40] = $global_config['PHIR']['ratio']=='default'? round( ($sumPHIR/$count), 3).$global_config['PHIR']['unit']:($global_config['PHIR']['ratio']=='percent'?round( ($sumPHIR/$count), 3).'%':round( ($sumPHIR/$count), 3).':1');
                $res['合计'][40] = ($phir == 0 || $sumPHIR==0  ) ? '0' :  ($global_config['PHIR']['ratio']=='default'? round( ($sumPHIR/$phir), 3).$global_config['PHIR']['unit']:($global_config['PHIR']['ratio']=='percent'?round( ($sumPHIR/$phir)*100, 3).'%':round( ($sumPHIR/$phir), 3).':1') );
                $res['合计'][41] = $sumPHIR;
                $res['合计'][42] = $phir;
                $res['合计'][43] = '合计';


                break;
            case 'juniorMiddleSchool':
                $res = [];
                $index = 1;
                $sumJHETR = 0;
                $sumJHBTR = 0;
                $sumJHATR = 0;
                $sumJSRAR = 0;
                $sumJSMAR = 0;
                $sumJSMR = 0;
                $sumJHIR = 0;
                $jhetr = 0;
                $jhbtr = 0;
                $jhatr = 0;
                $jsrar = 0;
                $jsmar = 0;
                $jsmr = 0;
                $jhir = 0;
                foreach ($sheetData as $value) {
                    if (in_array($value->user_id.$value->school.$value->school_type.$value->found_ind, $repeat)) {
                        continue;
                    }
                    //并发重复数据处理
                    $repeat[] = $value->user_id.$value->school.$value->school_type.$value->found_ind;
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    $value->found_ind=='JHETR' && $res[$value->school][2] = $value->found_ind=='JHETR' ? $value->basic_val:'';
                    $value->found_ind=='JHETR' && $res[$value->school][3] = $value->found_ind=='JHETR' ? $value->standard_val:'';
                    $value->found_ind=='JHETR' && $res[$value->school][4] = $value->found_ind=='JHETR' ? $value->found_val:'';
                    $value->found_ind=='JHETR' && $res[$value->school][5] = $value->found_ind=='JHETR' ? $value->found_divisor:'';
                    $value->found_ind=='JHETR' && $res[$value->school][6] = $value->found_ind=='JHETR' ? $value->found_divider:'';
                    $value->found_ind=='JHETR' && $res[$value->school][7] = $value->found_ind=='JHETR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JHBTR' && $res[$value->school][8] = $value->found_ind=='JHBTR' ? $value->basic_val:'';
                    $value->found_ind=='JHBTR' && $res[$value->school][9] = $value->found_ind=='JHBTR' ? $value->standard_val:'';
                    $value->found_ind=='JHBTR' && $res[$value->school][10] = $value->found_ind=='JHBTR' ? $value->found_val:'';
                    $value->found_ind=='JHBTR' && $res[$value->school][11] = $value->found_ind=='JHBTR' ? $value->found_divisor:'';
                    $value->found_ind=='JHBTR' && $res[$value->school][12] = $value->found_ind=='JHBTR' ? $value->found_divider:'';
                    $value->found_ind=='JHBTR' && $res[$value->school][13] = $value->found_ind=='JHBTR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JHATR' && $res[$value->school][14] = $value->found_ind=='JHATR' ? $value->basic_val:'';
                    $value->found_ind=='JHATR' && $res[$value->school][15] = $value->found_ind=='JHATR' ? $value->standard_val:'';
                    $value->found_ind=='JHATR' && $res[$value->school][16] = $value->found_ind=='JHATR' ? $value->found_val:'';
                    $value->found_ind=='JHATR' && $res[$value->school][17] = $value->found_ind=='JHATR' ? $value->found_divisor:'';
                    $value->found_ind=='JHATR' && $res[$value->school][18] = $value->found_ind=='JHATR' ? $value->found_divider:'';
                    $value->found_ind=='JHATR' && $res[$value->school][19] = $value->found_ind=='JHATR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JSRAR' && $res[$value->school][20] = $value->found_ind=='JSRAR' ? $value->basic_val:'';
                    $value->found_ind=='JSRAR' && $res[$value->school][21] = $value->found_ind=='JSRAR' ? $value->standard_val:'';
                    $value->found_ind=='JSRAR' && $res[$value->school][22] = $value->found_ind=='JSRAR' ? $value->found_val:'';
                    $value->found_ind=='JSRAR' && $res[$value->school][23] = $value->found_ind=='JSRAR' ? $value->found_divisor:'';
                    $value->found_ind=='JSRAR' && $res[$value->school][24] = $value->found_ind=='JSRAR' ? $value->found_divider:'';
                    $value->found_ind=='JSRAR' && $res[$value->school][25] = $value->found_ind=='JSRAR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JSMAR' && $res[$value->school][26] = $value->found_ind=='JSMAR' ? $value->basic_val:'';
                    $value->found_ind=='JSMAR' && $res[$value->school][27] = $value->found_ind=='JSMAR' ? $value->standard_val:'';
                    $value->found_ind=='JSMAR' && $res[$value->school][28] = $value->found_ind=='JSMAR' ? $value->found_val:'';
                    $value->found_ind=='JSMAR' && $res[$value->school][29] = $value->found_ind=='JSMAR' ? $value->found_divisor:'';
                    $value->found_ind=='JSMAR' && $res[$value->school][30] = $value->found_ind=='JSMAR' ? $value->found_divider:'';
                    $value->found_ind=='JSMAR' && $res[$value->school][31] = $value->found_ind=='JSMAR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JSMR' && $res[$value->school][32] = $value->found_ind=='JSMR' ? $value->basic_val:'';
                    $value->found_ind=='JSMR' && $res[$value->school][33] = $value->found_ind=='JSMR' ? $value->standard_val:'';
                    $value->found_ind=='JSMR' && $res[$value->school][34] = $value->found_ind=='JSMR' ? $value->found_val:'';
                    $value->found_ind=='JSMR' && $res[$value->school][35] = $value->found_ind=='JSMR' ? $value->found_divisor:'';
                    $value->found_ind=='JSMR' && $res[$value->school][36] = $value->found_ind=='JSMR' ? $value->found_divider:'';
                    $value->found_ind=='JSMR' && $res[$value->school][37] = $value->found_ind=='JSMR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    $value->found_ind=='JHIR' && $res[$value->school][38] = $value->found_ind=='JHIR' ? $value->basic_val:'';
                    $value->found_ind=='JHIR' && $res[$value->school][39] = $value->found_ind=='JHIR' ? $value->standard_val:'';
                    $value->found_ind=='JHIR' && $res[$value->school][40] = $value->found_ind=='JHIR' ? $value->found_val:'';
                    $value->found_ind=='JHIR' && $res[$value->school][41] = $value->found_ind=='JHIR' ? $value->found_divisor:'';
                    $value->found_ind=='JHIR' && $res[$value->school][42] = $value->found_ind=='JHIR' ? $value->found_divider:'';
                    $value->found_ind=='JHIR' && $res[$value->school][43] = $value->found_ind=='JHIR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    
                    //---JHETR
                    // if ($value->found_ind=='JHETR' && ($global_config['JHETR']['ratio']=='default' || $global_config['JHETR']['ratio']=='percent')) {
                    //     $jhetr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='JHETR' && $global_config['JHETR']['ratio']=='scale') {
                    //     $jhetr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='JHETR'&& $value->found_val) && $sumJHETR += $jhetr;
                    ($value->found_ind=='JHETR'&& $value->found_divisor) && $sumJHETR += floatval($value->found_divisor);
                    ($value->found_ind=='JHETR'&& $value->found_divider) && $jhetr += floatval($value->found_divider);
                    //-----JHETR
                    //---JHBTR
                    // if ($value->found_ind=='JHBTR' && ($global_config['JHBTR']['ratio']=='default' || $global_config['JHBTR']['ratio']=='percent')) {
                    //     $jhbtr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='JHBTR' && $global_config['JHBTR']['ratio']=='scale') {
                    //     $jhbtr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='JHBTR'&& $value->found_val) && $sumJHBTR += $jhbtr;
                    ($value->found_ind=='JHBTR'&& $value->found_divisor) && $sumJHBTR += floatval($value->found_divisor);
                    ($value->found_ind=='JHBTR'&& $value->found_divider) && $jhbtr += floatval($value->found_divider);
                    //-----JHBTR
                    //---JHATR
                    // if ($value->found_ind=='JHATR' && ($global_config['JHATR']['ratio']=='default' || $global_config['JHATR']['ratio']=='percent')) {
                    //     $jhatr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='JHATR' && $global_config['JHATR']['ratio']=='scale') {
                    //     $jhatr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='JHATR'&& $value->found_val) && $sumJHATR+= $jhatr;
                    ($value->found_ind=='JHATR'&& $value->found_divisor) && $sumJHATR += floatval($value->found_divisor);
                    ($value->found_ind=='JHATR'&& $value->found_divider) && $jhatr += floatval($value->found_divider);
                    //-----JHATR
                    //---JSRAR
                    // if ($value->found_ind=='JSRAR' && ($global_config['JSRAR']['ratio']=='default' || $global_config['JSRAR']['ratio']=='percent')) {
                    //     $jsrar = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='JSRAR' && $global_config['JSRAR']['ratio']=='scale') {
                    //     $jsrar = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='JSRAR'&& $value->found_val) && $sumJSRAR += $jsrar;
                    ($value->found_ind=='JSRAR'&& $value->found_divisor) && $sumJSRAR += floatval($value->found_divisor);
                    ($value->found_ind=='JSRAR'&& $value->found_divider) && $jsrar += floatval($value->found_divider);
                    //-----JSRAR
                    //---JSMAR
                    // if ($value->found_ind=='JSMAR' && ($global_config['JSMAR']['ratio']=='default' || $global_config['JSMAR']['ratio']=='percent')) {
                    //     $jsmar = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='JSMAR' && $global_config['JSMAR']['ratio']=='scale') {
                    //     $jsmar = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='JSMAR'&& $value->found_val) && $sumJSMAR += $jsmar;
                    ($value->found_ind=='JSMAR'&& $value->found_divisor) && $sumJSMAR += floatval($value->found_divisor);
                    ($value->found_ind=='JSMAR'&& $value->found_divider) && $jsmar += floatval($value->found_divider);
                    //-----JSMAR
                    //---JSMR
                    // if ($value->found_ind=='JSMR' && ($global_config['JSMR']['ratio']=='default' || $global_config['JSMR']['ratio']=='percent')) {
                    //     $jsmr = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='JSMR' && $global_config['JSMR']['ratio']=='scale') {
                    //     $jsmr = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='JSMR'&& $value->found_val) && $sumJSMR += $jsmr;
                    ($value->found_ind=='JSMR'&& $value->found_divisor) && $sumJSMR += floatval($value->found_divisor);
                    ($value->found_ind=='JSMR'&& $value->found_divider) && $jsmr += floatval($value->found_divider);
                    //-----JSMR
                    //---JHIR
                    // if ($value->found_ind=='JHIR' && ($global_config['JHIR']['ratio']=='default' || $global_config['JHIR']['ratio']=='percent')) {
                    //     $jhir = floatval($value->found_val);
                    // }
                    // if ($value->found_ind=='JHIR' && $global_config['JHIR']['ratio']=='scale') {
                    //     $jhir = explode(':', $value->found_val)[0];
                    // }
                    // ($value->found_ind=='JHIR'&& $value->found_val) && $sumJHIR += $jhir;
                    ($value->found_ind=='JHIR'&& $value->found_divisor) && $sumJHIR += floatval($value->found_divisor);
                    ($value->found_ind=='JHIR'&& $value->found_divider) && $jhir += floatval($value->found_divider);
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
                // $res['合计'][4] = $global_config['JHETR']['ratio']=='default'? round( ($sumJHETR/$count), 3).$global_config['JHETR']['unit']:($global_config['JHETR']['ratio']=='percent'?round( ($sumJHETR/$count), 3).'%':round( ($sumJHETR/$count), 3).':1');
                $res['合计'][4] =  ($jhetr == 0 || $sumJHETR==0  ) ? '0' :  ($global_config['JHETR']['ratio']=='default'? round( ($sumJHETR/$jhetr), 3).$global_config['JHETR']['unit']:($global_config['JHETR']['ratio']=='percent'?round( ($sumJHETR/$jhetr)*100, 3).'%':round( ($sumJHETR/$jhetr), 3).':1') );
                $res['合计'][5] = $sumJHETR ;
                $res['合计'][6] = $jhetr;
                $res['合计'][7] = '合计';

                $res['合计'][8] = $global_config['JHBTR']['basic_val'];
                $res['合计'][9] = $global_config['JHBTR']['standard_val'];
                // $res['合计'][10] = $global_config['JHBTR']['ratio']=='default'? round( ($sumJHBTR/$count), 3).$global_config['JHBTR']['unit']:($global_config['JHBTR']['ratio']=='percent'?round( ($sumJHBTR/$count), 3).'%':round( ($sumJHBTR/$count), 3).':1');
                $res['合计'][10] =  ($jhbtr == 0 || $sumJHBTR==0  ) ? '0' :  ($global_config['JHBTR']['ratio']=='default'? round( ($sumJHBTR/$jhbtr), 3).$global_config['JHBTR']['unit']:($global_config['JHBTR']['ratio']=='percent'?round( ($sumJHBTR/$jhbtr)*100, 3).'%':round( ($sumJHBTR/$jhbtr), 3).':1') );
                $res['合计'][11] = $sumJHBTR;
                $res['合计'][12] = $jhbtr;
                $res['合计'][13] = '合计';

                $res['合计'][14] = $global_config['JHATR']['basic_val'];
                $res['合计'][15] = $global_config['JHATR']['standard_val'];
                // $res['合计'][16] = $global_config['JHATR']['ratio']=='default'? round( ($sumJHATR/$count), 3).$global_config['JHATR']['unit']:($global_config['JHATR']['ratio']=='percent'?round( ($sumJHATR/$count), 3).'%':round( ($sumJHATR/$count), 3).':1');
                $res['合计'][16] =  ($jhatr == 0 || $sumJHATR==0  ) ? '0' :  ($global_config['JHATR']['ratio']=='default'? round( ($sumJHATR/$jhatr), 3).$global_config['JHATR']['unit']:($global_config['JHATR']['ratio']=='percent'?round( ($sumJHATR/$jhatr)*100, 3).'%':round( ($sumJHATR/$jhatr), 3).':1') );
                $res['合计'][17] = $sumJHATR;
                $res['合计'][18] = $jhatr;
                $res['合计'][19] = '合计';

                $res['合计'][20] = $global_config['JSRAR']['basic_val'];
                $res['合计'][21] = $global_config['JSRAR']['standard_val'];
                // $res['合计'][22] = $global_config['JSRAR']['ratio']=='default'? round( ($sumJSRAR/$count), 3).$global_config['JSRAR']['unit']:($global_config['JSRAR']['ratio']=='percent'?round( ($sumJSRAR/$count), 3).'%':round( ($sumJSRAR/$count), 3).':1');
                $res['合计'][22] = ($jsrar == 0 || $sumJSRAR==0  ) ? '0' :  ($global_config['JSRAR']['ratio']=='default'? round( ($sumJSRAR/$jsrar), 3).$global_config['JSRAR']['unit']:($global_config['JSRAR']['ratio']=='percent'?round( ($sumJSRAR/$jsrar)*100, 3).'%':round( ($sumJSRAR/$jsrar), 3).':1') );
                $res['合计'][23] = $sumJSRAR;
                $res['合计'][24] = $jsrar;
                $res['合计'][25] = '合计';

                $res['合计'][26] = $global_config['JSMAR']['basic_val'];
                $res['合计'][27] = $global_config['JSMAR']['standard_val'];
                // $res['合计'][28] = $global_config['JSMAR']['ratio']=='default'? round( ($sumJSMAR/$count), 3).$global_config['JSMAR']['unit']:($global_config['JSMAR']['ratio']=='percent'?round( ($sumJSMAR/$count), 3).'%':round( ($sumJSMAR/$count), 3).':1');
                $res['合计'][28] = ($jsmar == 0 || $sumJSMAR==0  ) ? '0' :  ($global_config['JSMAR']['ratio']=='default'? round( ($sumJSMAR/$jsmar), 3).$global_config['JSMAR']['unit']:($global_config['JSMAR']['ratio']=='percent'?round( ($sumJSMAR/$jsmar)*100, 3).'%':round( ($sumJSMAR/$jsmar), 3).':1') );
                $res['合计'][29] =$sumJSMAR;
                $res['合计'][30] = $jsmar;
                $res['合计'][31] = '合计';

                $res['合计'][32] = $global_config['JSMR']['basic_val'];
                $res['合计'][33] = $global_config['JSMR']['standard_val'];
                // $res['合计'][34] = $global_config['JSMR']['ratio']=='default'? round( ($sumJSMR/$count), 3).$global_config['JSMR']['unit']:($global_config['JSMR']['ratio']=='percent'?round( ($sumJSMR/$count), 3).'%':round( ($sumJSMR/$count), 3).':1');
                 $res['合计'][34] = ($jsmr == 0 || $sumJSMR==0  ) ? '0' :  ($global_config['JSMR']['ratio']=='default'? round( ($sumJSMR/$jsmr), 3).$global_config['JSMR']['unit']:($global_config['JSMR']['ratio']=='percent'?round( ($sumJSMR/$jsmr)*100, 3).'%':round( ($sumJSMR/$jsmr), 3).':1') );
                $res['合计'][35] = $sumJSMR;
                $res['合计'][36] = $jsmr;
                $res['合计'][37] = '合计';

                $res['合计'][38] = $global_config['JHIR']['basic_val'];
                $res['合计'][39] = $global_config['JHIR']['standard_val'];
                // $res['合计'][40] = $global_config['JHIR']['ratio']=='default'? round( ($sumJHIR/$count), 3).$global_config['JHIR']['unit']:($global_config['JHIR']['ratio']=='percent'?round( ($sumJHIR/$count), 3).'%':round( ($sumJHIR/$count), 3).':1');
                $res['合计'][40] = ($jhir == 0 || $sumJHIR==0  ) ? '0' :  ($global_config['JHIR']['ratio']=='default'? round( ($sumJHIR/$jhir), 3).$global_config['JHIR']['unit']:($global_config['JHIR']['ratio']=='percent'?round( ($sumJHIR/$jhir)*100, 3).'%':round( ($sumJHIR/$jhir), 3).':1') );
                $res['合计'][41] = $sumJHIR;
                $res['合计'][42] = $jhir;
                $res['合计'][43] = '合计';
                break;
            case 'nineYearCon':
                $res = [];
                $index = 1;
                $sumNHETR = 0;
                $sumNHBTR = 0;
                $sumNHATR = 0;
                $sumNSRAR = 0;
                $sumNSMAR = 0;
                $sumNSMR = 0;
                $sumNHIR = 0;
                $nhetr = 0;
                $nhbtr = 0;
                $nhatr = 0;
                $nsrar = 0;
                $nsmar = 0;
                $nsmr = 0;
                $nhir = 0;
                Log::info('nineYearCon sheetData----------------');
                Log::info($sheetData);
                Log::info('----------------');
                foreach ($sheetData as $value) {
                    if (in_array($value->user_id.$value->school.$value->school_type.$value->found_ind, $repeat)) {
                        continue;
                    }
                    //并发重复数据处理
                    $repeat[] = $value->user_id.$value->school.$value->school_type.$value->found_ind;
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    //小学
                    $value->found_ind=='NHETR' && $res[$value->school][2] = $value->found_ind=='NHETR' ? $value->basic_val:'';
                    $value->found_ind=='NHETR' && $res[$value->school][3] = $value->found_ind=='NHETR' ? $value->standard_val:'';
                    $value->found_ind=='NHETR' && $res[$value->school][4] = $value->found_ind=='NHETR' ? $value->found_val:'';
                    $value->found_ind=='NHETR' && $res[$value->school][5] = $value->found_ind=='NHETR' ? $value->found_divisor:'';
                    $value->found_ind=='NHETR' && $res[$value->school][6] = $value->found_ind=='NHETR' ? $value->found_divider:'';
                    $value->found_ind=='NHETR' && $res[$value->school][7] = $value->found_ind=='NHETR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    //初中
                    $value->found_ind=='NJHETR' && $res[$value->school][2] = $value->found_ind=='NJHETR' ? $value->basic_val:'';
                    $value->found_ind=='NJHETR' && $res[$value->school][3] = $value->found_ind=='NJHETR' ? $value->standard_val:'';
                    $value->found_ind=='NJHETR' && $res[$value->school][4] = $value->found_ind=='NJHETR' ? $value->found_val:'';
                    $value->found_ind=='NJHETR' && $res[$value->school][5] = $value->found_ind=='NJHETR' ? $value->found_divisor:'';
                    $value->found_ind=='NJHETR' && $res[$value->school][6] = $value->found_ind=='NJHETR' ? $value->found_divider:'';
                    $value->found_ind=='NJHETR' && $res[$value->school][7] = $value->found_ind=='NJHETR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    
                    //小学
                    $value->found_ind=='NHBTR' && $res[$value->school][8] = $value->found_ind=='NHBTR' ? $value->basic_val:'';
                    $value->found_ind=='NHBTR' && $res[$value->school][9] = $value->found_ind=='NHBTR' ? $value->standard_val:'';
                    $value->found_ind=='NHBTR' && $res[$value->school][10] = $value->found_ind=='NHBTR' ? $value->found_val:'';
                    $value->found_ind=='NHBTR' && $res[$value->school][11] = $value->found_ind=='NHBTR' ? $value->found_divisor:'';
                    $value->found_ind=='NHBTR' && $res[$value->school][12] = $value->found_ind=='NHBTR' ? $value->found_divider:'';
                    $value->found_ind=='NHBTR' && $res[$value->school][13] = $value->found_ind=='NHBTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    //初中
                    $value->found_ind=='NJHBTR' && $res[$value->school][8] = $value->found_ind=='NJHBTR' ? $value->basic_val:'';
                    $value->found_ind=='NJHBTR' && $res[$value->school][9] = $value->found_ind=='NJHBTR' ? $value->standard_val:'';
                    $value->found_ind=='NJHBTR' && $res[$value->school][10] = $value->found_ind=='NJHBTR' ? $value->found_val:'';
                    $value->found_ind=='NJHBTR' && $res[$value->school][11] = $value->found_ind=='NJHBTR' ? $value->found_divisor:'';
                    $value->found_ind=='NJHBTR' && $res[$value->school][12] = $value->found_ind=='NJHBTR' ? $value->found_divider:'';
                    $value->found_ind=='NJHBTR' && $res[$value->school][13] = $value->found_ind=='NJHBTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    
                    //小学
                    $value->found_ind=='NHATR' && $res[$value->school][14] = $value->found_ind=='NHATR' ? $value->basic_val:'';
                    $value->found_ind=='NHATR' && $res[$value->school][15] = $value->found_ind=='NHATR' ? $value->standard_val:'';
                    $value->found_ind=='NHATR' && $res[$value->school][16] = $value->found_ind=='NHATR' ? $value->found_val:'';
                    $value->found_ind=='NHATR' && $res[$value->school][17] = $value->found_ind=='NHATR' ? $value->found_divisor:'';
                    $value->found_ind=='NHATR' && $res[$value->school][18] = $value->found_ind=='NHATR' ? $value->found_divider:'';
                    $value->found_ind=='NHATR' && $res[$value->school][19] = $value->found_ind=='NHATR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    //初中
                    $value->found_ind=='NJHATR' && $res[$value->school][14] = $value->found_ind=='NJHATR' ? $value->basic_val:'';
                    $value->found_ind=='NJHATR' && $res[$value->school][15] = $value->found_ind=='NJHATR' ? $value->standard_val:'';
                    $value->found_ind=='NJHATR' && $res[$value->school][16] = $value->found_ind=='NJHATR' ? $value->found_val:'';
                    $value->found_ind=='NJHATR' && $res[$value->school][17] = $value->found_ind=='NJHATR' ? $value->found_divisor:'';
                    $value->found_ind=='NJHATR' && $res[$value->school][18] = $value->found_ind=='NJHATR' ? $value->found_divider:'';
                    $value->found_ind=='NJHATR' && $res[$value->school][19] = $value->found_ind=='NJHATR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    //小学
                    $value->found_ind=='NSRAR' && $res[$value->school][20] = $value->found_ind=='NSRAR' ? $value->basic_val:'';
                    $value->found_ind=='NSRAR' && $res[$value->school][21] = $value->found_ind=='NSRAR' ? $value->standard_val:'';
                    $value->found_ind=='NSRAR' && $res[$value->school][22] = $value->found_ind=='NSRAR' ? $value->found_val:'';
                    $value->found_ind=='NSRAR' && $res[$value->school][23] = $value->found_ind=='NSRAR' ? $value->found_divisor:'';
                    $value->found_ind=='NSRAR' && $res[$value->school][24] = $value->found_ind=='NSRAR' ? $value->found_divider:'';
                    $value->found_ind=='NSRAR' && $res[$value->school][25] = $value->found_ind=='NSRAR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    //初中
                    $value->found_ind=='NJSRAR' && $res[$value->school][20] = $value->found_ind=='NJSRAR' ? $value->basic_val:'';
                    $value->found_ind=='NJSRAR' && $res[$value->school][21] = $value->found_ind=='NJSRAR' ? $value->standard_val:'';
                    $value->found_ind=='NJSRAR' && $res[$value->school][22] = $value->found_ind=='NJSRAR' ? $value->found_val:'';
                    $value->found_ind=='NJSRAR' && $res[$value->school][23] = $value->found_ind=='NJSRAR' ? $value->found_divisor:'';
                    $value->found_ind=='NJSRAR' && $res[$value->school][24] = $value->found_ind=='NJSRAR' ? $value->found_divider:'';
                    $value->found_ind=='NJSRAR' && $res[$value->school][25] = $value->found_ind=='NJSRAR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    //小学
                    $value->found_ind=='NSMAR' && $res[$value->school][26] = $value->found_ind=='NSMAR' ? $value->basic_val:'';
                    $value->found_ind=='NSMAR' && $res[$value->school][27] = $value->found_ind=='NSMAR' ? $value->standard_val:'';
                    $value->found_ind=='NSMAR' && $res[$value->school][28] = $value->found_ind=='NSMAR' ? $value->found_val:'';
                    $value->found_ind=='NSMAR' && $res[$value->school][29] = $value->found_ind=='NSMAR' ? $value->found_divisor:'';
                    $value->found_ind=='NSMAR' && $res[$value->school][30] = $value->found_ind=='NSMAR' ? $value->found_divider:'';
                    $value->found_ind=='NSMAR' && $res[$value->school][31] = $value->found_ind=='NSMAR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    //初中
                    $value->found_ind=='NJSMAR' && $res[$value->school][26] = $value->found_ind=='NJSMAR' ? $value->basic_val:'';
                    $value->found_ind=='NJSMAR' && $res[$value->school][27] = $value->found_ind=='NJSMAR' ? $value->standard_val:'';
                    $value->found_ind=='NJSMAR' && $res[$value->school][28] = $value->found_ind=='NJSMAR' ? $value->found_val:'';
                    $value->found_ind=='NJSMAR' && $res[$value->school][29] = $value->found_ind=='NJSMAR' ? $value->found_divisor:'';
                    $value->found_ind=='NJSMAR' && $res[$value->school][30] = $value->found_ind=='NJSMAR' ? $value->found_divider:'';
                    $value->found_ind=='NJSMAR' && $res[$value->school][31] = $value->found_ind=='NJSMAR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    //小学
                    $value->found_ind=='NSMR' && $res[$value->school][32] = $value->found_ind=='NSMR' ? $value->basic_val:'';
                    $value->found_ind=='NSMR' && $res[$value->school][33] = $value->found_ind=='NSMR' ? $value->standard_val:'';
                    $value->found_ind=='NSMR' && $res[$value->school][34] = $value->found_ind=='NSMR' ? $value->found_val:'';
                    $value->found_ind=='NSMR' && $res[$value->school][35] = $value->found_ind=='NSMR' ? $value->found_divisor:'';
                    $value->found_ind=='NSMR' && $res[$value->school][36] = $value->found_ind=='NSMR' ? $value->found_divider:'';
                    $value->found_ind=='NSMR' && $res[$value->school][37] = $value->found_ind=='NSMR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    //初中
                    $value->found_ind=='NJSMR' && $res[$value->school][32] = $value->found_ind=='NJSMR' ? $value->basic_val:'';
                    $value->found_ind=='NJSMR' && $res[$value->school][33] = $value->found_ind=='NJSMR' ? $value->standard_val:'';
                    $value->found_ind=='NJSMR' && $res[$value->school][34] = $value->found_ind=='NJSMR' ? $value->found_val:'';
                    $value->found_ind=='NJSMR' && $res[$value->school][35] = $value->found_ind=='NJSMR' ? $value->found_divisor:'';
                    $value->found_ind=='NJSMR' && $res[$value->school][36] = $value->found_ind=='NJSMR' ? $value->found_divider:'';
                    $value->found_ind=='NJSMR' && $res[$value->school][37] = $value->found_ind=='NJSMR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    //小学
                    $value->found_ind=='NHIR' && $res[$value->school][38] = $value->found_ind=='NHIR' ? $value->basic_val:'';
                    $value->found_ind=='NHIR' && $res[$value->school][39] = $value->found_ind=='NHIR' ? $value->standard_val:'';
                    $value->found_ind=='NHIR' && $res[$value->school][40] = $value->found_ind=='NHIR' ? $value->found_val:'';
                    $value->found_ind=='NHIR' && $res[$value->school][41] = $value->found_ind=='NHIR' ? $value->found_divisor:'';
                    $value->found_ind=='NHIR' && $res[$value->school][42] = $value->found_ind=='NHIR' ? $value->found_divider:'';
                    $value->found_ind=='NHIR' && $res[$value->school][43] = $value->found_ind=='NHIR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    //初中
                    $value->found_ind=='NJHIR' && $res[$value->school][38] = $value->found_ind=='NJHIR' ? $value->basic_val:'';
                    $value->found_ind=='NJHIR' && $res[$value->school][39] = $value->found_ind=='NJHIR' ? $value->standard_val:'';
                    $value->found_ind=='NJHIR' && $res[$value->school][40] = $value->found_ind=='NJHIR' ? $value->found_val:'';
                    $value->found_ind=='NJHIR' && $res[$value->school][41] = $value->found_ind=='NJHIR' ? $value->found_divisor:'';
                    $value->found_ind=='NJHIR' && $res[$value->school][42] = $value->found_ind=='NJHIR' ? $value->found_divider:'';
                    $value->found_ind=='NJHIR' && $res[$value->school][43] = $value->found_ind=='NJHIR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    
                    //---NHETR \NJHETR
                    // if ( ($value->found_ind=='NHETR'||$value->found_ind=='NJHETR') && ($global_config['NHETR']['ratio']=='default' || $global_config['NHETR']['ratio']=='percent')) {
                    //     $nhetr = floatval($value->found_val);
                    // }
                    // if ( ($value->found_ind=='NHETR'||$value->found_ind=='NJHETR') && $global_config['NHETR']['ratio']=='scale') {
                    //     $nhetr = explode(':', $value->found_val)[0];
                    // }
                    // ( ($value->found_ind=='NHETR'||$value->found_ind=='NJHETR') && $value->found_val) && $sumNHETR += $nhetr;
                    ( ($value->found_ind=='NHETR'||$value->found_ind=='NJHETR') && $value->found_divisor)&& $sumNHETR += floatval($value->found_divisor);
                    ( ($value->found_ind=='NHETR'||$value->found_ind=='NJHETR') && $value->found_divider) && $nhetr += floatval($value->found_divider);
                    //-----NHETR \NJHETR
                    //---NHBTR \NJHBTR
                    // if ( ($value->found_ind=='NHBTR'||$value->found_ind=='NJHBTR') && ($global_config['NHBTR']['ratio']=='default' || $global_config['NHBTR']['ratio']=='percent')) {
                    //     $nhbtr = floatval($value->found_val);
                    // }
                    // if ( ($value->found_ind=='NHBTR'||$value->found_ind=='NJHBTR') && $global_config['NHBTR']['ratio']=='scale') {
                    //     $nhbtr = explode(':', $value->found_val)[0];
                    // }
                    // ( ($value->found_ind=='NHBTR'||$value->found_ind=='NJHBTR')&& $value->found_val) && $sumNHBTR += $nhbtr;
                    ( ($value->found_ind=='NHBTR'||$value->found_ind=='NJHBTR') && $value->found_divisor)&& $sumNHBTR += floatval($value->found_divisor);
                    ( ($value->found_ind=='NHBTR'||$value->found_ind=='NJHBTR') && $value->found_divider) && $nhbtr += floatval($value->found_divider);
                    //-----NHBTR \NJHBTR
                    //---NHATR \NJHATR
                    // if ( ($value->found_ind=='NHATR'||$value->found_ind=='NJHATR') && ($global_config['NHATR']['ratio']=='default' || $global_config['NHATR']['ratio']=='percent')) {
                    //     $nhatr = floatval($value->found_val);
                    // }
                    // if ( ($value->found_ind=='NHATR'||$value->found_ind=='NJHATR') && $global_config['NHATR']['ratio']=='scale') {
                    //     $nhatr = explode(':', $value->found_val)[0];
                    // }
                    // ( ($value->found_ind=='NHATR'||$value->found_ind=='NJHATR')&& $value->found_val) && $sumNHATR+= $nhatr;
                    ( ($value->found_ind=='NHATR'||$value->found_ind=='NJHATR') && $value->found_divisor)&& $sumNHATR += floatval($value->found_divisor);
                    ( ($value->found_ind=='NHATR'||$value->found_ind=='NJHATR') && $value->found_divider) && $nhatr += floatval($value->found_divider);
                    //-----NHATR \NJHATR
                    //---NSRAR \NJSRAR
                    // if ( ($value->found_ind=='NSRAR'||$value->found_ind=='NJSRAR') && ($global_config['NSRAR']['ratio']=='default' || $global_config['NSRAR']['ratio']=='percent')) {
                    //     $nsrar = floatval($value->found_val);
                    // }
                    // if ( ($value->found_ind=='NSRAR'||$value->found_ind=='NJSRAR') && $global_config['NSRAR']['ratio']=='scale') {
                    //     $nsrar = explode(':', $value->found_val)[0];
                    // }
                    // ( ($value->found_ind=='NSRAR'||$value->found_ind=='NJSRAR')&& $value->found_val) && $sumNSRAR += $nsrar;
                    ( ($value->found_ind=='NSRAR'||$value->found_ind=='NJSRAR') && $value->found_divisor)&& $sumNSRAR += floatval($value->found_divisor);
                    ( ($value->found_ind=='NJSRAR'||$value->found_ind=='NJSRAR') && $value->found_divider) && $nsrar += floatval($value->found_divider);
                    //-----NSRAR \NJSRAR
                    //---NSMAR \NJSMAR
                    // if ( ($value->found_ind=='NSMAR'||$value->found_ind=='NJSMAR') && ($global_config['NSMAR']['ratio']=='default' || $global_config['NSMAR']['ratio']=='percent')) {
                    //     $nsmar = floatval($value->found_val);
                    // }
                    // if ( ($value->found_ind=='NSMAR'||$value->found_ind=='NJSMAR') && $global_config['NSMAR']['ratio']=='scale') {
                    //     $nsmar = explode(':', $value->found_val)[0];
                    // }
                    // ( ($value->found_ind=='NSMAR'||$value->found_ind=='NJSMAR')&& $value->found_val) && $sumNSMAR += $nsmar;
                    ( ($value->found_ind=='NSMAR'||$value->found_ind=='NJSMAR') && $value->found_divisor)&& $sumNSMAR += floatval($value->found_divisor);
                    ( ($value->found_ind=='NSMAR'||$value->found_ind=='NJSMAR') && $value->found_divider) && $nsmar += floatval($value->found_divider);
                    //-----NSMAR \NJSMAR
                    //---NSMR \NJSMR
                    // if ( ($value->found_ind=='NSMR'||$value->found_ind=='NJSMR') && ($global_config['NSMR']['ratio']=='default' || $global_config['NSMR']['ratio']=='percent')) {
                    //     $nsmr = floatval($value->found_val);
                    // }
                    // if ( ($value->found_ind=='NSMR'||$value->found_ind=='NJSMR') && $global_config['NSMR']['ratio']=='scale') {
                    //     $nsmr = explode(':', $value->found_val)[0];
                    // }
                    // ( ($value->found_ind=='NSMR'||$value->found_ind=='NJSMR')&& $value->found_val) && $sumNSMR += $nsmr;
                    ( ($value->found_ind=='NSMR'||$value->found_ind=='NJSMR') && $value->found_divisor)&& $sumNSMR += floatval($value->found_divisor);
                    ( ($value->found_ind=='NSMR'||$value->found_ind=='NJSMR') && $value->found_divider) && $nsmr += floatval($value->found_divider);
                    //-----NSMR \NJSMR
                    //---NHIR\NJHIR
                    // if ( ($value->found_ind=='NHIR'||$value->found_ind=='NJHIR') && ($global_config['NHIR']['ratio']=='default' || $global_config['NHIR']['ratio']=='percent')) {
                    //     $nhir = floatval($value->found_val);
                    // }
                    // if ( ($value->found_ind=='NHIR'||$value->found_ind=='NJHIR') && $global_config['NHIR']['ratio']=='scale') {
                    //     $nhir = explode(':', $value->found_val)[0];
                    // }
                    // ( ($value->found_ind=='NHIR'||$value->found_ind=='NJHIR')&& $value->found_val) && $sumNHIR += $nhir;
                    ( ($value->found_ind=='NHIR'||$value->found_ind=='NJHIR') && $value->found_divisor)&& $sumNHIR += floatval($value->found_divisor);
                    ( ($value->found_ind=='NHIR'||$value->found_ind=='NJHIR') && $value->found_divider) && $nhir += floatval($value->found_divider);
                    //-----NHIR\NJHIR
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
                // $res['合计'][4] = $global_config['NHETR']['ratio']=='default'? round( ($sumNHETR/$count), 3).$global_config['NHETR']['unit']:($global_config['NHETR']['ratio']=='percent'?round( ($sumNHETR/$count), 3).'%':round( ($sumNHETR/$count), 3).':1');
                $res['合计'][4] = ($nhetr == 0 || $sumNHETR==0  ) ? '0' :   ($global_config['NHETR']['ratio']=='default'? round( ($sumNHETR/$nhetr), 3).$global_config['NHETR']['unit']:($global_config['NHETR']['ratio']=='percent'?round( ($sumNHETR/$nhetr)*100, 3).'%':round( ($sumNHETR/$nhetr), 3).':1') );
                $res['合计'][5] = $sumNHETR;
                $res['合计'][6] = $nhetr;
                $res['合计'][7] = '合计';

                $res['合计'][8] = $global_config['NHBTR']['basic_val'];
                $res['合计'][9] = $global_config['NHBTR']['standard_val'];
                // $res['合计'][10] = $global_config['NHBTR']['ratio']=='default'? round( ($sumNHBTR/$count), 3).$global_config['NHBTR']['unit']:($global_config['NHBTR']['ratio']=='percent'?round( ($sumNHBTR/$count), 3).'%':round( ($sumNHBTR/$count), 3).':1');
                $res['合计'][10] = ($nhbtr == 0 || $sumNHBTR==0  ) ? '0' :   ($global_config['NHBTR']['ratio']=='default'? round( ($sumNHBTR/$nhbtr), 3).$global_config['NHBTR']['unit']:($global_config['NHBTR']['ratio']=='percent'?round( ($sumNHBTR/$nhbtr)*100, 3).'%':round( ($sumNHBTR/$nhbtr), 3).':1') );
                $res['合计'][11] = $sumNHBTR;
                $res['合计'][12] = $nhbtr;
                $res['合计'][13] = '合计';

                $res['合计'][14] = $global_config['NHATR']['basic_val'];
                $res['合计'][15] = $global_config['NHATR']['standard_val'];
                // $res['合计'][16] = $global_config['NHATR']['ratio']=='default'? round( ($sumNHATR/$count), 3).$global_config['NHATR']['unit']:($global_config['NHATR']['ratio']=='percent'?round( ($sumNHATR/$count), 3).'%':round( ($sumNHATR/$count), 3).':1');
                $res['合计'][16] = ($nhatr == 0 || $sumNHATR==0  ) ? '0' :   ($global_config['NHATR']['ratio']=='default'? round( ($sumNHATR/$nhatr), 3).$global_config['NHATR']['unit']:($global_config['NHATR']['ratio']=='percent'?round( ($sumNHATR/$nhatr)*100, 3).'%':round( ($sumNHATR/$nhatr), 3).':1') );
                $res['合计'][17] = $sumNHATR;
                $res['合计'][18] = $nhatr;
                $res['合计'][19] = '合计';

                $res['合计'][20] = $global_config['NSRAR']['basic_val'];
                $res['合计'][21] = $global_config['NSRAR']['standard_val'];
                // $res['合计'][22] = $global_config['NSRAR']['ratio']=='default'? round( ($sumNSRAR/$count), 3).$global_config['NSRAR']['unit']:($global_config['NSRAR']['ratio']=='percent'?round( ($sumNSRAR/$count), 3).'%':round( ($sumNSRAR/$count), 3).':1');
                $res['合计'][22] = ($nsrar == 0 || $sumNSRAR==0  ) ? '0' :  ($global_config['NSRAR']['ratio']=='default'? round( ($sumNSRAR/$nsrar), 3).$global_config['NSRAR']['unit']:($global_config['NSRAR']['ratio']=='percent'?round( ($sumNSRAR/$nsrar)*100, 3).'%':round( ($sumNSRAR/$nsrar), 3).':1') );
                $res['合计'][23] = $sumNSRAR;
                $res['合计'][24] = $nsrar;
                $res['合计'][25] = '合计';

                $res['合计'][26] = $global_config['NSMAR']['basic_val'];
                $res['合计'][27] = $global_config['NSMAR']['standard_val'];
                // $res['合计'][28] = $global_config['NSMAR']['ratio']=='default'? round( ($sumNSMAR/$count), 3).$global_config['NSMAR']['unit']:($global_config['NSMAR']['ratio']=='percent'?round( ($sumNSMAR/$count), 3).'%':round( ($sumNSMAR/$count), 3).':1');
                $res['合计'][28] =  ($nsmar == 0 || $sumNSMAR==0  ) ? '0' :  ($global_config['NSMAR']['ratio']=='default'? round( ($sumNSMAR/$nsmar), 3).$global_config['NSMAR']['unit']:($global_config['NSMAR']['ratio']=='percent'?round( ($sumNSMAR/$nsmar)*100, 3).'%':round( ($sumNSMAR/$nsmar), 3).':1') );
                $res['合计'][29] = $sumNSMAR;
                $res['合计'][30] = $nsmar;
                $res['合计'][31] = '合计';

                $res['合计'][32] = $global_config['NSMR']['basic_val'];
                $res['合计'][33] = $global_config['NSMR']['standard_val'];
                // $res['合计'][34] = $global_config['NSMR']['ratio']=='default'? round( ($sumNSMR/$count), 3).$global_config['NSMR']['unit']:($global_config['NSMR']['ratio']=='percent'?round( ($sumNSMR/$count), 3).'%':round( ($sumNSMR/$count), 3).':1');
                $res['合计'][34] = ($nsmr == 0 || $sumNSMR==0  ) ? '0' :  ($global_config['NSMR']['ratio']=='default'? round( ($sumNSMR/$nsmr), 3).$global_config['NSMR']['unit']:($global_config['NSMR']['ratio']=='percent'?round( ($sumNSMR/$nsmr)*100, 3).'%':round( ($sumNSMR/$nsmr), 3).':1') );
                $res['合计'][35] = $sumNSMR;
                $res['合计'][36] = $nsmr;
                $res['合计'][37] = '合计';

                $res['合计'][38] = $global_config['NHIR']['basic_val'];
                $res['合计'][39] = $global_config['NHIR']['standard_val'];
                // $res['合计'][40] = $global_config['NHIR']['ratio']=='default'? round( ($sumNHIR/$count), 3).$global_config['NHIR']['unit']:($global_config['NHIR']['ratio']=='percent'?round( ($sumNHIR/$count), 3).'%':round( ($sumNHIR/$count), 3).':1');
                $res['合计'][40] = ($nhir == 0 || $sumNHIR==0  ) ? '0' :  ($global_config['NHIR']['ratio']=='default'? round( ($sumNHIR/$nhir), 3).$global_config['NHIR']['unit']:($global_config['NHIR']['ratio']=='percent'?round( ($sumNHIR/$nhir)*100, 3).'%':round( ($sumNHIR/$nhir), 3).':1') );
                $res['合计'][41] = $sumNHIR;
                $res['合计'][42] = $nhir;
                $res['合计'][43] = '合计';

                Log::info('nineYearCon res');
                Log::info($res);
                break;

            case 'twelveYearCon':
                $res = [];
                $index = 1;
                $sumTNHETR = 0;
                $sumTNHBTR = 0;
                $sumTNHATR = 0;
                $sumTNSRAR = 0;
                $sumTNSMAR = 0;
                $sumTNSMR = 0;
                $sumTNHIR = 0;
                $tnhetr = 0;
                $tnhbtr = 0;
                $tnhatr = 0;
                $tnsrar = 0;
                $tnsmar = 0;
                $tnsmr = 0;
                $tnhir = 0;
                foreach ($sheetData as $value) {
                    if (in_array($value->user_id.$value->school.$value->school_type.$value->found_ind, $repeat)) {
                        continue;
                    }
                    //并发重复数据处理
                    $repeat[] = $value->user_id.$value->school.$value->school_type.$value->found_ind;
                    $res[$value->school][0] = $index;
                    $res[$value->school][1] = $value->school;
                    //小学
                    $value->found_ind=='TNHETR' && $res[$value->school][2] = $value->found_ind=='TNHETR' ? $value->basic_val:'';
                    $value->found_ind=='TNHETR' && $res[$value->school][3] = $value->found_ind=='TNHETR' ? $value->standard_val:'';
                    $value->found_ind=='TNHETR' && $res[$value->school][4] = $value->found_ind=='TNHETR' ? $value->found_val:'';
                    $value->found_ind=='TNHETR' && $res[$value->school][5] = $value->found_ind=='TNHETR' ? $value->found_divisor:'';
                    $value->found_ind=='TNHETR' && $res[$value->school][6] = $value->found_ind=='TNHETR' ? $value->found_divider:'';
                    $value->found_ind=='TNHETR' && $res[$value->school][7] = $value->found_ind=='TNHETR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    //初中
                    $value->found_ind=='TNJHETR' && $res[$value->school][2] = $value->found_ind=='TNJHETR' ? $value->basic_val:'';
                    $value->found_ind=='TNJHETR' && $res[$value->school][3] = $value->found_ind=='TNJHETR' ? $value->standard_val:'';
                    $value->found_ind=='TNJHETR' && $res[$value->school][4] = $value->found_ind=='TNJHETR' ? $value->found_val:'';
                    $value->found_ind=='TNJHETR' && $res[$value->school][5] = $value->found_ind=='TNJHETR' ? $value->found_divisor:'';
                    $value->found_ind=='TNJHETR' && $res[$value->school][6] = $value->found_ind=='TNJHETR' ? $value->found_divider:'';
                    $value->found_ind=='TNJHETR' && $res[$value->school][7] = $value->found_ind=='TNJHETR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    
                    //小学
                    $value->found_ind=='TNHBTR' && $res[$value->school][8] = $value->found_ind=='TNHBTR' ? $value->basic_val:'';
                    $value->found_ind=='TNHBTR' && $res[$value->school][9] = $value->found_ind=='TNHBTR' ? $value->standard_val:'';
                    $value->found_ind=='TNHBTR' && $res[$value->school][10] = $value->found_ind=='TNHBTR' ? $value->found_val:'';
                    $value->found_ind=='TNHBTR' && $res[$value->school][11] = $value->found_ind=='TNHBTR' ? $value->found_divisor:'';
                    $value->found_ind=='TNHBTR' && $res[$value->school][12] = $value->found_ind=='TNHBTR' ? $value->found_divider:'';
                    $value->found_ind=='TNHBTR' && $res[$value->school][13] = $value->found_ind=='TNHBTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    //初中
                    $value->found_ind=='TNJHBTR' && $res[$value->school][8] = $value->found_ind=='TNJHBTR' ? $value->basic_val:'';
                    $value->found_ind=='TNJHBTR' && $res[$value->school][9] = $value->found_ind=='TNJHBTR' ? $value->standard_val:'';
                    $value->found_ind=='TNJHBTR' && $res[$value->school][10] = $value->found_ind=='TNJHBTR' ? $value->found_val:'';
                    $value->found_ind=='TNJHBTR' && $res[$value->school][11] = $value->found_ind=='TNJHBTR' ? $value->found_divisor:'';
                    $value->found_ind=='TNJHBTR' && $res[$value->school][12] = $value->found_ind=='TNJHBTR' ? $value->found_divider:'';
                    $value->found_ind=='TNJHBTR' && $res[$value->school][13] = $value->found_ind=='TNJHBTR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    
                    //小学
                    $value->found_ind=='TNHATR' && $res[$value->school][14] = $value->found_ind=='TNHATR' ? $value->basic_val:'';
                    $value->found_ind=='TNHATR' && $res[$value->school][15] = $value->found_ind=='TNHATR' ? $value->standard_val:'';
                    $value->found_ind=='TNHATR' && $res[$value->school][16] = $value->found_ind=='TNHATR' ? $value->found_val:'';
                    $value->found_ind=='TNHATR' && $res[$value->school][17] = $value->found_ind=='TNHATR' ? $value->found_divisor:'';
                    $value->found_ind=='TNHATR' && $res[$value->school][18] = $value->found_ind=='TNHATR' ? $value->found_divider:'';
                    $value->found_ind=='TNHATR' && $res[$value->school][19] = $value->found_ind=='TNHATR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    //初中
                    $value->found_ind=='TNJHATR' && $res[$value->school][14] = $value->found_ind=='TNJHATR' ? $value->basic_val:'';
                    $value->found_ind=='TNJHATR' && $res[$value->school][15] = $value->found_ind=='TNJHATR' ? $value->standard_val:'';
                    $value->found_ind=='TNJHATR' && $res[$value->school][16] = $value->found_ind=='TNJHATR' ? $value->found_val:'';
                    $value->found_ind=='TNJHATR' && $res[$value->school][17] = $value->found_ind=='TNJHATR' ? $value->found_divisor:'';
                    $value->found_ind=='TNJHATR' && $res[$value->school][18] = $value->found_ind=='TNJHATR' ? $value->found_divider:'';
                    $value->found_ind=='TNJHATR' && $res[$value->school][19] = $value->found_ind=='TNJHATR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    //小学
                    $value->found_ind=='TNSRAR' && $res[$value->school][20] = $value->found_ind=='TNSRAR' ? $value->basic_val:'';
                    $value->found_ind=='TNSRAR' && $res[$value->school][21] = $value->found_ind=='TNSRAR' ? $value->standard_val:'';
                    $value->found_ind=='TNSRAR' && $res[$value->school][22] = $value->found_ind=='TNSRAR' ? $value->found_val:'';
                    $value->found_ind=='TNSRAR' && $res[$value->school][23] = $value->found_ind=='TNSRAR' ? $value->found_divisor:'';
                    $value->found_ind=='TNSRAR' && $res[$value->school][24] = $value->found_ind=='TNSRAR' ? $value->found_divider:'';
                    $value->found_ind=='TNSRAR' && $res[$value->school][25] = $value->found_ind=='TNSRAR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    //初中
                    $value->found_ind=='TNJSRAR' && $res[$value->school][20] = $value->found_ind=='TNJSRAR' ? $value->basic_val:'';
                    $value->found_ind=='TNJSRAR' && $res[$value->school][21] = $value->found_ind=='TNJSRAR' ? $value->standard_val:'';
                    $value->found_ind=='TNJSRAR' && $res[$value->school][22] = $value->found_ind=='TNJSRAR' ? $value->found_val:'';
                    $value->found_ind=='TNJSRAR' && $res[$value->school][23] = $value->found_ind=='TNJSRAR' ? $value->found_divisor:'';
                    $value->found_ind=='TNJSRAR' && $res[$value->school][24] = $value->found_ind=='TNJSRAR' ? $value->found_divider:'';
                    $value->found_ind=='TNJSRAR' && $res[$value->school][25] = $value->found_ind=='TNJSRAR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    //小学
                    $value->found_ind=='TNSMAR' && $res[$value->school][26] = $value->found_ind=='TNSMAR' ? $value->basic_val:'';
                    $value->found_ind=='TNSMAR' && $res[$value->school][27] = $value->found_ind=='TNSMAR' ? $value->standard_val:'';
                    $value->found_ind=='TNSMAR' && $res[$value->school][28] = $value->found_ind=='TNSMAR' ? $value->found_val:'';
                    $value->found_ind=='TNSMAR' && $res[$value->school][29] = $value->found_ind=='TNSMAR' ? $value->found_divisor:'';
                    $value->found_ind=='TNSMAR' && $res[$value->school][30] = $value->found_ind=='TNSMAR' ? $value->found_divider:'';
                    $value->found_ind=='TNSMAR' && $res[$value->school][31] = $value->found_ind=='TNSMAR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    //初中
                    $value->found_ind=='TNJSMAR' && $res[$value->school][26] = $value->found_ind=='TNJSMAR' ? $value->basic_val:'';
                    $value->found_ind=='TNJSMAR' && $res[$value->school][27] = $value->found_ind=='TNJSMAR' ? $value->standard_val:'';
                    $value->found_ind=='TNJSMAR' && $res[$value->school][28] = $value->found_ind=='TNJSMAR' ? $value->found_val:'';
                    $value->found_ind=='TNJSMAR' && $res[$value->school][29] = $value->found_ind=='TNJSMAR' ? $value->found_divisor:'';
                    $value->found_ind=='TNJSMAR' && $res[$value->school][30] = $value->found_ind=='TNJSMAR' ? $value->found_divider:'';
                    $value->found_ind=='TNJSMAR' && $res[$value->school][31] = $value->found_ind=='TNJSMAR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    //小学
                    $value->found_ind=='TNSMR' && $res[$value->school][32] = $value->found_ind=='TNSMR' ? $value->basic_val:'';
                    $value->found_ind=='TNSMR' && $res[$value->school][33] = $value->found_ind=='TNSMR' ? $value->standard_val:'';
                    $value->found_ind=='TNSMR' && $res[$value->school][34] = $value->found_ind=='TNSMR' ? $value->found_val:'';
                    $value->found_ind=='TNSMR' && $res[$value->school][35] = $value->found_ind=='TNSMR' ? $value->found_divisor:'';
                    $value->found_ind=='TNSMR' && $res[$value->school][36] = $value->found_ind=='TNSMR' ? $value->found_divider:'';
                    $value->found_ind=='TNSMR' && $res[$value->school][37] = $value->found_ind=='TNSMR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    //初中
                    $value->found_ind=='TNJSMR' && $res[$value->school][32] = $value->found_ind=='TNJSMR' ? $value->basic_val:'';
                    $value->found_ind=='TNJSMR' && $res[$value->school][33] = $value->found_ind=='TNJSMR' ? $value->standard_val:'';
                    $value->found_ind=='TNJSMR' && $res[$value->school][34] = $value->found_ind=='TNJSMR' ? $value->found_val:'';
                    $value->found_ind=='TNJSMR' && $res[$value->school][35] = $value->found_ind=='TNJSMR' ? $value->found_divisor:'';
                    $value->found_ind=='TNJSMR' && $res[$value->school][36] = $value->found_ind=='TNJSMR' ? $value->found_divider:'';
                    $value->found_ind=='TNJSMR' && $res[$value->school][37] = $value->found_ind=='TNJSMR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    //小学
                    $value->found_ind=='TNHIR' && $res[$value->school][38] = $value->found_ind=='TNHIR' ? $value->basic_val:'';
                    $value->found_ind=='TNHIR' && $res[$value->school][39] = $value->found_ind=='TNHIR' ? $value->standard_val:'';
                    $value->found_ind=='TNHIR' && $res[$value->school][40] = $value->found_ind=='TNHIR' ? $value->found_val:'';
                    $value->found_ind=='TNHIR' && $res[$value->school][41] = $value->found_ind=='TNHIR' ? $value->found_divisor:'';
                    $value->found_ind=='TNHIR' && $res[$value->school][42] = $value->found_ind=='TNHIR' ? $value->found_divider:'';
                    $value->found_ind=='TNHIR' && $res[$value->school][43] = $value->found_ind=='TNHIR' ? ($value->is_standard == 1?'达标':'不达标'):'';
                    //初中
                    $value->found_ind=='TNJHIR' && $res[$value->school][38] = $value->found_ind=='TNJHIR' ? $value->basic_val:'';
                    $value->found_ind=='TNJHIR' && $res[$value->school][39] = $value->found_ind=='TNJHIR' ? $value->standard_val:'';
                    $value->found_ind=='TNJHIR' && $res[$value->school][40] = $value->found_ind=='TNJHIR' ? $value->found_val:'';
                    $value->found_ind=='TNJHIR' && $res[$value->school][41] = $value->found_ind=='TNJHIR' ? $value->found_divisor:'';
                    $value->found_ind=='TNJHIR' && $res[$value->school][42] = $value->found_ind=='TNJHIR' ? $value->found_divider:'';
                    $value->found_ind=='TNJHIR' && $res[$value->school][43] = $value->found_ind=='TNJHIR' ? ($value->is_standard == 1?'达标':'不达标'):'';

                    
                    //---TNHETR \TNJHETR
                    // if ( ($value->found_ind=='TNHETR'||$value->found_ind=='TNJHETR') && ($global_config['TNHETR']['ratio']=='default' || $global_config['TNHETR']['ratio']=='percent')) {
                    //     $tnhetr = floatval($value->found_val);
                    // }
                    // if ( ($value->found_ind=='TNHETR'||$value->found_ind=='TNJHETR') && $global_config['TNHETR']['ratio']=='scale') {
                    //     $tnhetr = explode(':', $value->found_val)[0];
                    // }
                    // ( ($value->found_ind=='TNHETR'||$value->found_ind=='TNJHETR') && $value->found_val) && $sumTNHETR += $tnhetr;
                    ( ($value->found_ind=='TNHETR'||$value->found_ind=='TNJHETR') && $value->found_divisor)&& $sumTNHETR += floatval($value->found_divisor);
                    ( ($value->found_ind=='TNHETR'||$value->found_ind=='TNJHETR') && $value->found_divider) && $tnhetr += floatval($value->found_divider);
                    //-----TNHETR \TNJHETR
                    //---TNHBTR \TNJHBTR
                    // if ( ($value->found_ind=='TNHBTR'||$value->found_ind=='TNJHBTR') && ($global_config['TNHBTR']['ratio']=='default' || $global_config['TNHBTR']['ratio']=='percent')) {
                    //     $tnhbtr = floatval($value->found_val);
                    // }
                    // if ( ($value->found_ind=='TNHBTR'||$value->found_ind=='TNJHBTR') && $global_config['TNHBTR']['ratio']=='scale') {
                    //     $tnhbtr = explode(':', $value->found_val)[0];
                    // }
                    // ( ($value->found_ind=='TNHBTR'||$value->found_ind=='TNJHBTR')&& $value->found_val) && $sumTNHBTR += $tnhbtr;
                    ( ($value->found_ind=='TNHBTR'||$value->found_ind=='TNJHBTR') && $value->found_divisor)&& $sumTNHBTR += floatval($value->found_divisor);
                    ( ($value->found_ind=='TNHBTR'||$value->found_ind=='TNJHBTR') && $value->found_divider) && $tnhbtr += floatval($value->found_divider);
                    //-----TNHBTR \TNJHBTR
                    //---TNHATR \TNJHATR
                    // if ( ($value->found_ind=='TNHATR'||$value->found_ind=='TNJHATR') && ($global_config['TNHATR']['ratio']=='default' || $global_config['TNHATR']['ratio']=='percent')) {
                    //     $tnhatr = floatval($value->found_val);
                    // }
                    // if ( ($value->found_ind=='TNHATR'||$value->found_ind=='TNJHATR') && $global_config['TNHATR']['ratio']=='scale') {
                    //     $tnhatr = explode(':', $value->found_val)[0];
                    // }
                    // ( ($value->found_ind=='TNHATR'||$value->found_ind=='TNJHATR')&& $value->found_val) && $sumTNHATR+= $tnhatr;
                    ( ($value->found_ind=='TNHATR'||$value->found_ind=='TNJHATR') && $value->found_divisor)&& $sumTNHATR += floatval($value->found_divisor);
                    ( ($value->found_ind=='TNHATR'||$value->found_ind=='TNJHATR') && $value->found_divider) && $tnhatr += floatval($value->found_divider);
                    //-----TNHATR \TNJHATR
                    //---TNSRAR \TNJSRAR
                    // if ( ($value->found_ind=='TNSRAR'||$value->found_ind=='TNJSRAR') && ($global_config['TNSRAR']['ratio']=='default' || $global_config['TNSRAR']['ratio']=='percent')) {
                    //     $tnsrar = floatval($value->found_val);
                    // }
                    // if ( ($value->found_ind=='TNSRAR'||$value->found_ind=='TNJSRAR') && $global_config['TNSRAR']['ratio']=='scale') {
                    //     $tnsrar = explode(':', $value->found_val)[0];
                    // }
                    // ( ($value->found_ind=='TNSRAR'||$value->found_ind=='TNJSRAR')&& $value->found_val) && $sumTNSRAR += $tnsrar;
                    ( ($value->found_ind=='TNSRAR'||$value->found_ind=='TNJSRAR') && $value->found_divisor)&& $sumTNSRAR += floatval($value->found_divisor);
                    ( ($value->found_ind=='TNSRAR'||$value->found_ind=='TNJSRAR') && $value->found_divider) && $tnsrar += floatval($value->found_divider);
                    //-----TNSRAR \TNJSRAR
                    //---TNSMAR \TNJSMAR
                    // if ( ($value->found_ind=='TNSMAR'||$value->found_ind=='TNJSMAR') && ($global_config['TNSMAR']['ratio']=='default' || $global_config['TNSMAR']['ratio']=='percent')) {
                    //     $tnsmar = floatval($value->found_val);
                    // }
                    // if ( ($value->found_ind=='TNSMAR'||$value->found_ind=='TNJSMAR') && $global_config['TNSMAR']['ratio']=='scale') {
                    //     $tnsmar = explode(':', $value->found_val)[0];
                    // }
                    // ( ($value->found_ind=='TNSMAR'||$value->found_ind=='TNJSMAR')&& $value->found_val) && $sumTNSMAR += $tnsmar;
                    ( ($value->found_ind=='TNSMAR'||$value->found_ind=='TNJSMAR') && $value->found_divisor)&& $sumTNSMAR += floatval($value->found_divisor);
                    ( ($value->found_ind=='TNSMAR'||$value->found_ind=='TNJSMAR') && $value->found_divider) && $tnsmar += floatval($value->found_divider);
                    //-----TNSMAR \TNJSMAR
                    //---TNSMR \TNJSMR
                    // if ( ($value->found_ind=='TNSMR'||$value->found_ind=='TNJSMR') && ($global_config['TNSMR']['ratio']=='default' || $global_config['TNSMR']['ratio']=='percent')) {
                    //     $tnsmr = floatval($value->found_val);
                    // }
                    // if ( ($value->found_ind=='TNSMR'||$value->found_ind=='TNJSMR') && $global_config['TNSMR']['ratio']=='scale') {
                    //     $tnsmr = explode(':', $value->found_val)[0];
                    // }
                    // ( ($value->found_ind=='TNSMR'||$value->found_ind=='TNJSMR')&& $value->found_val) && $sumTNSMR += $tnsmr;
                    ( ($value->found_ind=='TNSMR'||$value->found_ind=='TNJSMR') && $value->found_divisor)&& $sumTNSMR += floatval($value->found_divisor);
                    ( ($value->found_ind=='TNSMR'||$value->found_ind=='TNJSMR') && $value->found_divider) && $tnsmr += floatval($value->found_divider);
                    //-----TNSMR \TNJSMR
                    //---TNHIR\TNJHIR
                    // if ( ($value->found_ind=='TNHIR'||$value->found_ind=='TNJHIR') && ($global_config['TNHIR']['ratio']=='default' || $global_config['TNHIR']['ratio']=='percent')) {
                    //     $tnhir = floatval($value->found_val);
                    // }
                    // if ( ($value->found_ind=='TNHIR'||$value->found_ind=='TNJHIR') && $global_config['TNHIR']['ratio']=='scale') {
                    //     $tnhir = explode(':', $value->found_val)[0];
                    // }
                    // ( ($value->found_ind=='TNHIR'||$value->found_ind=='TNJHIR')&& $value->found_val) && $sumTNHIR += $tnhir;
                    ( ($value->found_ind=='TNHIR'||$value->found_ind=='TNJHIR') && $value->found_divisor)&& $sumTNHIR += floatval($value->found_divisor);
                    ( ($value->found_ind=='TNHIR'||$value->found_ind=='TNJHIR') && $value->found_divider) && $tnhir += floatval($value->found_divider);
                    //-----TNHIR\TNJHIR
                    $index++;
                }
                if (!$res) {
                    break;
                }
                $count = count($res);
                $res['合计'][0] = '';
                $res['合计'][1] = '合计';

                $res['合计'][2] = $global_config['TNHETR']['basic_val'];
                $res['合计'][3] = $global_config['TNHETR']['standard_val'];
                // $res['合计'][4] = $global_config['TNHETR']['ratio']=='default'? round( ($sumTNHETR/$count), 3).$global_config['TNHETR']['unit']:($global_config['TNHETR']['ratio']=='percent'?round( ($sumTNHETR/$count), 3).'%':round( ($sumTNHETR/$count), 3).':1');
                $res['合计'][4] = ($tnhetr == 0 || $sumTNHETR==0  ) ? '0' : ( $global_config['TNHETR']['ratio']=='default'? round( ($sumTNHETR/$tnhetr), 3).$global_config['TNHETR']['unit']:($global_config['TNHETR']['ratio']=='percent'?round( ($sumTNHETR/$tnhetr)*100, 3).'%':round( ($sumTNHETR/$tnhetr), 3).':1') );
                $res['合计'][5] = $sumTNHETR;
                $res['合计'][6] = $tnhetr;
                $res['合计'][7] = '合计';

                $res['合计'][8] = $global_config['TNHBTR']['basic_val'];
                $res['合计'][9] = $global_config['TNHBTR']['standard_val'];
                // $res['合计'][10] = $global_config['TNHBTR']['ratio']=='default'? round( ($sumTNHBTR/$count), 3).$global_config['TNHBTR']['unit']:($global_config['TNHBTR']['ratio']=='percent'?round( ($sumTNHBTR/$count), 3).'%':round( ($sumTNHBTR/$count), 3).':1');
                $res['合计'][10] = ($tnhbtr == 0 || $sumTNHBTR==0  ) ? '0' :  ($global_config['TNHBTR']['ratio']=='default'? round( ($sumTNHBTR/$tnhbtr), 3).$global_config['TNHBTR']['unit']:($global_config['TNHBTR']['ratio']=='percent'?round( ($sumTNHBTR/$tnhbtr)*100, 3).'%':round( ($sumTNHBTR/$tnhbtr), 3).':1') );
                $res['合计'][11] = $sumTNHBTR;
                $res['合计'][12] = $tnhbtr;
                $res['合计'][13] = '合计';

                $res['合计'][14] = $global_config['TNHATR']['basic_val'];
                $res['合计'][15] = $global_config['TNHATR']['standard_val'];
                // $res['合计'][16] = $global_config['TNHATR']['ratio']=='default'? round( ($sumTNHATR/$count), 3).$global_config['TNHATR']['unit']:($global_config['TNHATR']['ratio']=='percent'?round( ($sumTNHATR/$count), 3).'%':round( ($sumTNHATR/$count), 3).':1');
                $res['合计'][16] = ($tnhatr == 0 || $sumTNHATR==0  ) ? '0' :   ($global_config['TNHATR']['ratio']=='default'? round( ($sumTNHATR/$tnhatr), 3).$global_config['TNHATR']['unit']:($global_config['TNHATR']['ratio']=='percent'?round( ($sumTNHATR/$tnhatr)*100, 3).'%':round( ($sumTNHATR/$tnhatr), 3).':1') );

                $res['合计'][17] = $sumTNHATR;
                $res['合计'][18] = $tnhatr;
                $res['合计'][19] = '合计';

                $res['合计'][20] = $global_config['TNSRAR']['basic_val'];
                $res['合计'][21] = $global_config['TNSRAR']['standard_val'];
                // $res['合计'][22] = $global_config['TNSRAR']['ratio']=='default'? round( ($sumTNSRAR/$count), 3).$global_config['TNSRAR']['unit']:($global_config['TNSRAR']['ratio']=='percent'?round( ($sumTNSRAR/$count), 3).'%':round( ($sumTNSRAR/$count), 3).':1');
                $res['合计'][22] = ($tnsrar == 0 || $sumTNSRAR==0  ) ? '0' :  ($global_config['TNSRAR']['ratio']=='default'? round( ($sumTNSRAR/$tnsrar), 3).$global_config['TNSRAR']['unit']:($global_config['TNSRAR']['ratio']=='percent'?round( ($sumTNSRAR/$tnsrar)*100, 3).'%':round( ($sumTNSRAR/$tnsrar), 3).':1') );

                $res['合计'][23] = $sumTNSRAR;
                $res['合计'][24] = $tnsrar;
                $res['合计'][25] = '合计';

                $res['合计'][26] = $global_config['TNSMAR']['basic_val'];
                $res['合计'][27] = $global_config['TNSMAR']['standard_val'];
                // $res['合计'][28] = $global_config['TNSMAR']['ratio']=='default'? round( ($sumTNSMAR/$count), 3).$global_config['TNSMAR']['unit']:($global_config['TNSMAR']['ratio']=='percent'?round( ($sumTNSMAR/$count), 3).'%':round( ($sumTNSMAR/$count), 3).':1');
                $res['合计'][28] = ($tnsmar == 0 || $sumTNSMAR==0  ) ? '0' :   ($global_config['TNSMAR']['ratio']=='default'? round( ($sumTNSMAR/$tnsmar), 3).$global_config['TNSMAR']['unit']:($global_config['TNSMAR']['ratio']=='percent'?round( ($sumTNSMAR/$tnsmar)*100, 3).'%':round( ($sumTNSMAR/$tnsmar), 3).':1') );

                $res['合计'][29] = $sumTNSMAR;
                $res['合计'][30] = $tnsmar;
                $res['合计'][31] = '合计';

                $res['合计'][32] = $global_config['TNSMR']['basic_val'];
                $res['合计'][33] = $global_config['TNSMR']['standard_val'];
                // $res['合计'][34] = $global_config['TNSMR']['ratio']=='default'? round( ($sumTNSMR/$count), 3).$global_config['TNSMR']['unit']:($global_config['TNSMR']['ratio']=='percent'?round( ($sumTNSMR/$count), 3).'%':round( ($sumTNSMR/$count), 3).':1');
                $res['合计'][34] = ($tnsmr == 0 || $sumTNSMR==0  ) ? '0' :   ($global_config['TNSMR']['ratio']=='default'? round( ($sumTNSMR/$tnsmr), 3).$global_config['TNSMR']['unit']:($global_config['TNSMR']['ratio']=='percent'?round( ($sumTNSMR/$tnsmr)*100, 3).'%':round( ($sumTNSMR/$tnsmr), 3).':1') );

                $res['合计'][35] = $sumTNSMR;
                $res['合计'][36] = $tnsmr;
                $res['合计'][37] = '合计';

                $res['合计'][38] = $global_config['TNHIR']['basic_val'];
                $res['合计'][39] = $global_config['TNHIR']['standard_val'];
                // $res['合计'][40] = $global_config['TNHIR']['ratio']=='default'? round( ($sumTNHIR/$count), 3).$global_config['TNHIR']['unit']:($global_config['TNHIR']['ratio']=='percent'?round( ($sumTNHIR/$count), 3).'%':round( ($sumTNHIR/$count), 3).':1');
                $res['合计'][40] = ($tnhir == 0 || $sumTNHIR==0  ) ? '0' :  ($global_config['TNHIR']['ratio']=='default'? round( ($sumTNHIR/$tnhir), 3).$global_config['TNHIR']['unit']:($global_config['TNHIR']['ratio']=='percent'?round( ($sumTNHIR/$tnhir)*100, 3).'%':round( ($sumTNHIR/$tnhir), 3).':1') );

                $res['合计'][41] = $sumTNHIR;
                $res['合计'][42] = $tnhir;
                $res['合计'][43] = '合计';

                // Log::info('twelveYearCon res');
                // Log::info($res);
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

    //处理全国义务教育优质均衡县校际均衡情况
    /**
     * 求：平均值 x_ (sum_found_ind/students_sum)*100 / (sum_found_ind/students_sum)  指标
     * 列：∑(xi-x)2*Pi  （x-x_）*（x-x_）* 学生数 学校
     * 系数 = sqrt(sum ( ∑(xi-x)2*Pi )) / (x_*sqrt(students_sum)); 指标
     * @param  [type] $sheetData [description]
     * @return [type]            [description]
     */
    public function __getSituationQuery($sheetData)
    {
        if (!$sheetData) {
            return [];
        }
        $global_config = config('ixport.SCHOOL_IMPORT_FOUND_INDEX');
        $primarySchoolArr = [];
        $juniorMiddleSchoolArr = [];
        foreach ($sheetData as $key => $value) {
            if ($value->school_type == 'primarySchool') {
                $primarySchoolArr[] = $value;
            }
            if ($value->school_type == 'juniorMiddleSchool') {
                $juniorMiddleSchoolArr[] = $value;
            }
        }
        $res = [];
        //小学七项 平均值 、 系数
        if ($primarySchoolArr) {

            $prepeat = [];
            $pres = [];
            $sumPHETR = $sumPHBTR = $sumPHATR = $sumPSRAR = $sumPSMAR = $sumPSMR = $sumPHIR = $sumStudents = 0;

            foreach ($primarySchoolArr as $pk => $value) {
                if (in_array($value->user_id.$value->school.$value->school_type.$value->found_ind, $prepeat)) {
                    continue;
                }
                //并发重复数据处理
                $prepeat[] = $value->user_id.$value->school.$value->school_type.$value->found_ind;

                $pres[$pk] = $value;

                ($value->found_ind=='PHETR' && $value->found_divider) &&  $sumStudents += $value->found_ind=='PHETR' ? $value->found_divider : 0;//取每个学校的学生数

                //---PHETR
                ($value->found_ind=='PHETR' && $value->found_divisor) && $sumPHETR += $value->found_ind=='PHETR' ? $value->found_divisor : 0;
                //-----PHETR
                //---PHBTR
                ($value->found_ind=='PHBTR' && $value->found_divisor) && $sumPHBTR += $value->found_ind=='PHBTR' ? $value->found_divisor : 0;
                //-----PHBTR
                //---PHATR
                ($value->found_ind=='PHATR' && $value->found_divisor) && $sumPHATR += $value->found_ind=='PHATR' ? $value->found_divisor : 0;
                //-----PHATR
                //---PSRAR
                ($value->found_ind=='PSRAR' && $value->found_divisor) && $sumPSRAR += $value->found_ind=='PSRAR' ? $value->found_divisor : 0;
                //-----PSRAR
                //---PSMAR
                ($value->found_ind=='PSMAR' && $value->found_divisor) && $sumPSMAR += $value->found_ind=='PSMAR' ? $value->found_divisor : 0;
                //-----PSMAR
                //---PSMR
                ($value->found_ind=='PSMR' && $value->found_divisor) && $sumPSMR += $value->found_ind=='PSMR' ? $value->found_divisor : 0;
                //-----PSMR
                //---PHIR
                ($value->found_ind=='PHIR' && $value->found_divisor) && $sumPHIR += $value->found_ind=='PHIR' ? $value->found_divisor : 0;
                //-----PHIR
            }
            
            $averagePHETR =  $sumPHETR == 0 ? 0:round( ($sumPHETR/$sumStudents), 3);//每百生高于规定学历教师数 * 100
            $averagePHBTR =  $sumPHBTR == 0 ? 0:round( ($sumPHBTR/$sumStudents), 3);//每百生骨干教师数 * 100
            $averagePHATR =  $sumPHATR == 0 ? 0:round( ($sumPHATR/$sumStudents), 3);//每百生体育、艺术专任教师数 * 100
            $averagePSRAR =  $sumPSRAR == 0 ? 0:round( ($sumPSRAR/$sumStudents), 3);//生均教学及辅助用房面积
            $averagePSMAR =  $sumPSMAR == 0 ? 0:round( ($sumPSMAR/$sumStudents), 3);//生均体育运动场馆面积
            $averagePSMR  =  $sumPSMR == 0 ? 0:round( ($sumPSMR/$sumStudents), 3);//生均教学仪器设备值
            $averagePHIR  =  $sumPHIR == 0 ? 0:round( ($sumPHIR/$sumStudents), 3);//每百名学生拥有网络多媒体教室数 * 100
            //小学 ∑(xi-x)2*Pi  （x-x_）*（x-x_）* 学生数 
            $avSumPHETR = $avSumPHBTR = $avSumPHATR = $avSumPSRAR = $avSumPSMAR = $avSumPSMR = $avSumPHIR = 0;
            foreach ($pres as $prk => $value) {
                //---PHETR
                if ($value->found_ind=='PHETR') {
                    $avSumPHETR += ( floatval($value->found_val) - $averagePHETR ) * ( floatval($value->found_val) - $averagePHETR) * $value->found_divider;
                    Log::info('avSumPHETR row'. ( floatval($value->found_val) - $averagePHETR )  . 'averagePHETR'.$averagePHETR );
                }
                //-----PHETR
                //---PHBTR
                if ($value->found_ind=='PHBTR') {
                    $avSumPHBTR += ( floatval($value->found_val) - $averagePHBTR) * ( floatval($value->found_val) - $averagePHBTR) * $value->found_divider;
                }
                //-----PHBTR
                //---PHATR
                if ($value->found_ind=='PHATR') {
                    $avSumPHATR += ( floatval($value->found_val) - $averagePHATR) * ( floatval($value->found_val) - $averagePHATR) * $value->found_divider;
                }
                //-----PHATR
                //---PSRAR
                if ($value->found_ind=='PSRAR') {
                    $avSumPSRAR += ( floatval($value->found_val) - $averagePSRAR) * ( floatval($value->found_val) - $averagePSRAR) * $value->found_divider;
                }
                //-----PSRAR
                //---PSMAR
                if ($value->found_ind=='PSMAR') {
                    $avSumPSMAR += ( floatval($value->found_val) - $averagePSMAR) * ( floatval($value->found_val) - $averagePSMAR) * $value->found_divider;
                }
                //-----PSMAR
                //---PSMR
                if ($value->found_ind=='PSMR') {
                    $avSumPSMR += ( floatval($value->found_val) - $averagePSMR) * ( floatval($value->found_val) - $averagePSMR) * $value->found_divider;
                }
                //-----PSMR
                //---PHIR
                if ($value->found_ind=='PHIR') {
                    $avSumPHIR += ( floatval($value->found_val) - $averagePHIR) * ( floatval($value->found_val) - $averagePHIR) * $value->found_divider;
                }
                //-----PHIR
            }
            Log::info('----------------------------');
            Log::info('avSumPHETR'.$avSumPHETR);
            Log::info('sumStudents'.$sumStudents);
            Log::info('averagePHETR'.sqrt($avSumPHETR));
            Log::info('quotientPHETR'.round( sqrt($avSumPHETR) / ( $averagePHETR*sqrt($sumStudents) ) , 3));
            Log::info('----------------------------');
            //sqrt(sum ( ∑(xi-x)2*Pi )) / (x_*sqrt(students_sum)); 
            $quotientPHETR = round( sqrt($avSumPHETR) / ( $averagePHETR*sqrt($sumStudents) ) , 5)?:'0.0';
            $quotientPHBTR = round( sqrt($avSumPHBTR) / ( $averagePHBTR*sqrt($sumStudents) ) , 5)?:'0.0';
            $quotientPHATR = round( sqrt($avSumPHATR) / ( $averagePHATR*sqrt($sumStudents) ) , 5)?:'0.0';
            $quotientPSRAR = round( sqrt($avSumPSRAR) / ( $averagePSRAR*sqrt($sumStudents) ) , 5)?:'0.0';
            $quotientPSMAR = round( sqrt($avSumPSMAR) / ( $averagePSMAR*sqrt($sumStudents) ) , 5)?:'0.0';
            $quotientPSMR = round( sqrt($avSumPSMR) / ( $averagePSMR*sqrt($sumStudents) ) , 5)?:'0.0';
            $quotientPHIR = round( sqrt($avSumPHIR) / ( $averagePHIR*sqrt($sumStudents) ) , 5)?:'0.0';
            $comPaverage = round( ($quotientPHETR+$quotientPHBTR+$quotientPHATR+$quotientPSRAR+$quotientPSMAR+$quotientPSMR+$quotientPHIR)/7,3 )? :'0.0';

            Log::info('----------------------------');
            Log::info('quotientPHETR'.$quotientPHETR);
            Log::info('comPaverage'.$comPaverage);
            Log::info('----------------------------');
            $res = [
                ['宁波市','小学平均值',$averagePHETR,$averagePHBTR,$averagePHATR,$averagePSRAR,$averagePSMAR,$averagePSMR,$averagePHIR,'---'],
                ['','小学差异系数',$quotientPHETR,$quotientPHBTR,$quotientPHATR,$quotientPSRAR,$quotientPSMAR,$quotientPSMR,$quotientPHIR,$comPaverage]
            ];
        }
        
        if ($juniorMiddleSchoolArr) {

            //初中七项 平均值 、 系数
            $jrepeat = [];
            $jres = [];
            $sumJHETR = $sumJHBTR = $sumJHATR = $sumJSRAR = $sumJSMAR = $sumJSMR = $sumJHIR = $sumjStudents = 0;
            
            foreach ($juniorMiddleSchoolArr as $pk => $value) {
                if (in_array($value->user_id.$value->school.$value->school_type.$value->found_ind, $jrepeat)) {
                    continue;
                }
                //并发重复数据处理
                $jrepeat[] = $value->user_id.$value->school.$value->school_type.$value->found_ind;

                $jres[$pk] = $value;

                ($value->found_ind=='JHETR' && $value->found_divider) &&  $sumjStudents += $value->found_ind=='JHETR' ? $value->found_divider : 0;//取每个学校的学生数

                //---JHETR
                ($value->found_ind=='JHETR' && $value->found_divisor) && $sumJHETR += $value->found_ind=='JHETR' ? $value->found_divisor : 0;
                //-----JHETR
                //---JHBTR
                ($value->found_ind=='JHBTR' && $value->found_divisor) && $sumJHBTR += $value->found_ind=='JHBTR' ? $value->found_divisor : 0;
                //-----JHBTR
                //---JHATR
                ($value->found_ind=='JHATR' && $value->found_divisor) && $sumJHATR += $value->found_ind=='JHATR' ? $value->found_divisor : 0;
                //-----JHATR
                //---JSRAR
                ($value->found_ind=='JSRAR' && $value->found_divisor) && $sumJSRAR += $value->found_ind=='JSRAR' ? $value->found_divisor : 0;
                //-----JSRAR
                //---JSMAR
                ($value->found_ind=='JSMAR' && $value->found_divisor) && $sumJSMAR += $value->found_ind=='JSMAR' ? $value->found_divisor : 0;
                //-----JSMAR
                //---JSMR
                ($value->found_ind=='JSMR' && $value->found_divisor) && $sumJSMR += $value->found_ind=='JSMR' ? $value->found_divisor : 0;
                //-----JSMR
                //---JHIR
                ($value->found_ind=='JHIR' && $value->found_divisor) && $sumJHIR += $value->found_ind=='JHIR' ? $value->found_divisor : 0;
                //-----JHIR
            }
            
            $averageJHETR =  $sumJHETR == 0 ? 0:round( ($sumJHETR/$sumjStudents), 3);//每百生高于规定学历教师数 * 100
            $averageJHBTR =  $sumJHBTR == 0 ? 0:round( ($sumJHBTR/$sumjStudents), 3);//每百生骨干教师数 * 100
            $averageJHATR =  $sumJHATR == 0 ? 0:round( ($sumJHATR/$sumjStudents), 3);//每百生体育、艺术专任教师数 * 100
            $averageJSRAR =  $sumJSRAR == 0 ? 0:round( ($sumJSRAR/$sumjStudents), 3);//生均教学及辅助用房面积
            $averageJSMAR =  $sumJSMAR == 0 ? 0:round( ($sumJSMAR/$sumjStudents), 3);//生均体育运动场馆面积
            $averageJSMR  =  $sumJSMR == 0 ? 0:round( ($sumJSMR/$sumjStudents), 3);//生均教学仪器设备值
            $averageJHIR  =  $sumJHIR == 0 ? 0:round( ($sumJHIR/$sumjStudents), 3);//每百名学生拥有网络多媒体教室数 * 100
            //初中 ∑(xi-x)2*Pi  （x-x_）*（x-x_）* 学生数 
            $avSumJHETR = $avSumJHBTR = $avSumJHATR = $avSumJSRAR = $avSumJSMAR = $avSumJSMR = $avSumJHIR = 0;
            foreach ($jres as $prk => $value) {
                //---JHETR
                if ($value->found_ind=='JHETR') {
                    $avSumJHETR += ( floatval($value->found_val) - $averageJHETR) * ( floatval($value->found_val) - $averageJHETR) * $value->found_divider;
                }
                //-----JHETR
                //---JHBTR
                if ($value->found_ind=='JHBTR') {
                    $avSumJHBTR += ( floatval($value->found_val) - $averageJHBTR) * ( floatval($value->found_val) - $averageJHBTR) * $value->found_divider;
                }
                //-----JHBTR
                //---JHATR
                if ($value->found_ind=='JHATR') {
                    $avSumJHATR += ( floatval($value->found_val) - $averageJHATR) * ( floatval($value->found_val) - $averageJHATR) * $value->found_divider;
                }
                //-----JHATR
                //---JSRAR
                if ($value->found_ind=='JSRAR') {
                    $avSumJSRAR += ( floatval($value->found_val) - $averageJSRAR) * ( floatval($value->found_val) - $averageJSRAR) * $value->found_divider;
                }
                //-----JSRAR
                //---JSMAR
                if ($value->found_ind=='JSMAR') {
                    $avSumJSMAR += ( floatval($value->found_val) - $averageJSMAR) * ( floatval($value->found_val) - $averageJSMAR) * $value->found_divider;
                }
                //-----JSMAR
                //---JSMR
                if ($value->found_ind=='JSMR') {
                    $avSumJSMR += ( floatval($value->found_val) - $averageJSMR) * ( floatval($value->found_val) - $averageJSMR) * $value->found_divider;
                }
                //-----JSMR
                //---JHIR
                if ($value->found_ind=='JHIR') {
                    $avSumJHIR += ( floatval($value->found_val) - $averageJHIR) * ( floatval($value->found_val) - $averageJHIR) * $value->found_divider;
                }
                //-----JHIR
            }
            Log::info('----------------------------');
            Log::info('avSumJHETR'.$avSumJHETR);
            Log::info('sumjStudents'.$sumjStudents);
            Log::info('quotientJHETR'.round( sqrt($avSumJHETR) / ( $averageJHETR*sqrt($sumjStudents) ) , 3));
            Log::info('----------------------------');
            //sqrt(sum ( ∑(xi-x)2*Pi )) / (x_*sqrt(students_sum)); 
            $quotientJHETR = round( sqrt($avSumJHETR) / ( $averageJHETR*sqrt($sumjStudents) ) , 5)?:'0.0';
            $quotientJHBTR = round( sqrt($avSumJHBTR) / ( $averageJHBTR*sqrt($sumjStudents) ) , 5)?:'0.0';
            $quotientJHATR = round( sqrt($avSumJHATR) / ( $averageJHATR*sqrt($sumjStudents) ) , 5)?:'0.0';
            $quotientJSRAR = round( sqrt($avSumJSRAR) / ( $averageJSRAR*sqrt($sumjStudents) ) , 5)?:'0.0';
            $quotientJSMAR = round( sqrt($avSumJSMAR) / ( $averageJSMAR*sqrt($sumjStudents) ) , 5)?:'0.0';
            $quotientJSMR = round( sqrt($avSumJSMR) / ( $averageJSMR*sqrt($sumjStudents) ) , 5)?:'0.0';
            $quotientJHIR = round( sqrt($avSumJHIR) / ( $averageJHIR*sqrt($sumjStudents) ) , 5)?:'0.0';
            $comJPaverage = round( ($quotientJHETR+$quotientJHBTR+$quotientJHATR+$quotientJSRAR+$quotientJSMAR+$quotientJSMR+$quotientJHIR)/7,3 )? :'0.0';

            Log::info('----------------------------');
            Log::info('quotientJHIR'.$quotientJHIR);
            Log::info('avSumJHIR'.$avSumJHIR);
            Log::info('averageJHIR'.$averageJHIR);
            Log::info('----------------------------');

            $res[2] = ['宁波市','初中平均值',$averageJHETR,$averageJHBTR,$averageJHATR,$averageJSRAR,$averageJSMAR,$averageJSMR,$averageJHIR,'---'];
            $res[3] = ['','初中差异系数',$quotientJHETR,$quotientJHBTR,$quotientJHATR,$quotientJSRAR,$quotientJSMAR,$quotientJSMR,$quotientJHIR,$comJPaverage];
        }
        //处理只有其一的情况
        $res = array_values($res);
        
        Log::info($res);
        return $res;
    }

    private function Object2Array($object) { 
        if (is_object($object) || is_array($object)) {
            $array = array();
            foreach ($object as $key => $value) {
                if ($value == null) continue;
                $array[$key] = self::Object2Array($value);
            }
            return $array;
        }
        else {
            return $object;
        }
    }
}
