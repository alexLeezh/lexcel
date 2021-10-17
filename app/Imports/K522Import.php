<?php
namespace App\Imports;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Illuminate\Support\Facades\Log;
use App\Models\PreSheetData;

class K522Import implements  WithEvents
{
    private static $user_id;
    public function __construct(array $importData)
    {
        self::$user_id = $importData['user_id'];
    }
    
    public function registerEvents(): array
    {
        
    	return [
        	AfterSheet::class => [self::class, 'afterSheet'],
        ];
    }

    public static function afterSheet(AfterSheet $event) 
    {
        $school_type = app('session')->get('school_type');
        $school = app('session')->get('school');
        $report_hash = app('session')->get('report_hash');
        $preSheetData = new PreSheetData();
        switch ($school_type) {
            case 'kindergarten':
                
                break;
            case 'primarySchool':

                $areaF9 = $event->sheet->getCell("F9")->getValue();//运动场地面积
                $valuesO9 = $event->sheet->getCell("O9")->getValue();//教学仪器设备资产值
                $mL9 = $event->sheet->getCell("L9")->getValue();//网络多媒体教室数
                $mN9 = $event->sheet->getCell("N9")->getValue();//网络多媒体教室数
                $ms = $mL9 + $mN9;

                $arr = [
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'PSMAR','found_divisor'=>$areaF9,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'PSMR','found_divisor'=>$valuesO9,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'PHIR','found_divisor'=>$ms,'found_divider'=>0,'report_hash'=>$report_hash],

                ];

                foreach ($arr as $key => $value) {
                    $preSheetData = new PreSheetData();
                    //report_type found_ind found_divisor found_divider
                    $preSheetData->school_type = $value['school_type'];
                    $preSheetData->school = $value['school'];
                    $preSheetData->report_hash = $value['report_hash'];

                    $preSheetData->report_type = $value['report_type'];
                    $preSheetData->found_ind = $value['found_ind'];
                    $preSheetData->found_divisor = $value['found_divisor'];
                    $preSheetData->found_divider = $value['found_divider'];
                    $preSheetData->user_id = self::$user_id;
                    $preSheetData->save();
                }

                break;
            case 'juniorMiddleSchool':
                $areaF9 = $event->sheet->getCell("F9")->getValue();//运动场地面积
                $valuesO9 = $event->sheet->getCell("O9")->getValue();//教学仪器设备资产值
                $mL9 = $event->sheet->getCell("L9")->getValue();//网络多媒体教室数
                $mN9 = $event->sheet->getCell("N9")->getValue();//网络多媒体教室数
                $ms = $mL9 + $mN9;

                $arr = [
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'JSMAR','found_divisor'=>$areaF9,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'JSMR','found_divisor'=>$valuesO9,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'JHIR','found_divisor'=>$ms,'found_divider'=>0,'report_hash'=>$report_hash],

                ];

                foreach ($arr as $key => $value) {
                    $preSheetData = new PreSheetData();
                    //report_type found_ind found_divisor found_divider
                    $preSheetData->school_type = $value['school_type'];
                    $preSheetData->school = $value['school'];
                    $preSheetData->report_hash = $value['report_hash'];

                    $preSheetData->report_type = $value['report_type'];
                    $preSheetData->found_ind = $value['found_ind'];
                    $preSheetData->found_divisor = $value['found_divisor'];
                    $preSheetData->found_divider = $value['found_divider'];
                    $preSheetData->user_id = self::$user_id;
                    $preSheetData->save();
                }
                break;
            case 'highSchool':
                $mvalue = $event->sheet->getCell("O9")->getValue();//教学仪器设备值
                //report_type found_ind found_divisor found_divider
                $preSheetData->school_type = $school_type;
                $preSheetData->school = $school;
                $preSheetData->report_hash = $report_hash;

                $preSheetData->report_type = 'modern';
                $preSheetData->found_ind = 'HSMR';
                $preSheetData->found_divisor = $mvalue;//教学仪器设备值
                $preSheetData->user_id = self::$user_id;
                $preSheetData->save();
                break;
            case 'secondaryVocationalSchool':
                
                break;
            case 'nineYearCon':
                $areaF9 = $event->sheet->getCell("F9")->getValue();//运动场地面积
                $valuesO9 = $event->sheet->getCell("O9")->getValue();//教学仪器设备资产值
                $mL9 = $event->sheet->getCell("L9")->getValue();//网络多媒体教室数
                $mN9 = $event->sheet->getCell("N9")->getValue();//网络多媒体教室数
                $ms = $mL9 + $mN9;

                $arr = [
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'NSMAR','found_divisor'=>$areaF9,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'NJSMAR','found_divisor'=>$areaF9,'found_divider'=>0,'report_hash'=>$report_hash],


                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'NSMR','found_divisor'=>$valuesO9,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'NJSMR','found_divisor'=>$valuesO9,'found_divider'=>0,'report_hash'=>$report_hash],


                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'NJHIR','found_divisor'=>$ms,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'NHIR','found_divisor'=>$ms,'found_divider'=>0,'report_hash'=>$report_hash],

                ];

                foreach ($arr as $key => $value) {
                    $preSheetData = new PreSheetData();
                    //report_type found_ind found_divisor found_divider
                    $preSheetData->school_type = $value['school_type'];
                    $preSheetData->school = $value['school'];
                    $preSheetData->report_hash = $value['report_hash'];

                    $preSheetData->report_type = $value['report_type'];
                    $preSheetData->found_ind = $value['found_ind'];
                    $preSheetData->found_divisor = $value['found_divisor'];
                    $preSheetData->found_divider = $value['found_divider'];
                    $preSheetData->user_id = self::$user_id;
                    $preSheetData->save();
                }
                break;
            default:

                break;
        }
    	
    }
}