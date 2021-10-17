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

class K512Import implements  WithEvents
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
        switch ($school_type) {
            case 'kindergarten':
                
                break;
            case 'primarySchool':

                $areaE10 = $event->sheet->getCell("E10")->getValue();//教学及辅助用房面积
                $areaE16 = $event->sheet->getCell("E16")->getValue();//体育馆面积

                $areas = $areaE10 - $areaE16;
                $arr = [
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'PSRAR','found_divisor'=>$areas,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'PSMAR','found_divisor'=>$areaE16,'found_divider'=>0,'report_hash'=>$report_hash],

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
                $areaE10 = $event->sheet->getCell("E10")->getValue();//教学及辅助用房面积
                $areaE16 = $event->sheet->getCell("E16")->getValue();//体育馆面积

                $areas = $areaE10 - $areaE16;
                $arr = [
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'JSRAR','found_divisor'=>$areas,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'JSMAR','found_divisor'=>$areaE16,'found_divider'=>0,'report_hash'=>$report_hash],

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
                
                break;
            case 'secondaryVocationalSchool':
                
                break;

            case 'nineYearCon':
                $areaE10 = $event->sheet->getCell("E10")->getValue();//教学及辅助用房面积
                $areaE16 = $event->sheet->getCell("E16")->getValue();//体育馆面积

                $areas = $areaE10 - $areaE16;
                $arr = [
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'NSRAR','found_divisor'=>$areas,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'NJSRAR','found_divisor'=>$areas,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'NSMAR','found_divisor'=>$areaE16,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'NJSMAR','found_divisor'=>$areaE16,'found_divider'=>0,'report_hash'=>$report_hash],

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