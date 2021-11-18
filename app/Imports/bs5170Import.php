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

class bs5170Import implements  WithEvents
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
        Log::info('bs5170Import');
        $school_type = app('session')->get('school_type');
        $school = app('session')->get('school');
        $report_hash = app('session')->get('report_hash');
        switch ($school_type) {
            case 'kindergarten':
                
                break;
            case 'primarySchool':
                $areaC10 = $event->sheet->getCell("C10")->getValue();
                $areaC17 = $event->sheet->getCell("C17")->getValue();
                $areas = $areaC10 - $areaC17;
                Log::info('bs5176Import areaC17'.$areaC17);
                $arr = [

                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'balance','found_ind'=>'PSRAR','found_divisor'=>$areas,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'balance','found_ind'=>'PSMAR','found_divisor'=>$areaC17,'found_divider'=>0,'report_hash'=>$report_hash],


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
                $areaC10 = $event->sheet->getCell("C10")->getValue();
                $areaC17 = $event->sheet->getCell("C17")->getValue();
                $areas = $areaC10 - $areaC17;

                $arr = [

                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'balance','found_ind'=>'JSRAR','found_divisor'=>$areas,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'balance','found_ind'=>'JSMAR','found_divisor'=>$areaC17,'found_divider'=>0,'report_hash'=>$report_hash],



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
                $areaC10 = $event->sheet->getCell("C10")->getValue();
                $areaC17 = $event->sheet->getCell("C17")->getValue();
                $areas = $areaC10 - $areaC17;

                $arr = [

                    ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NSRAR','found_divisor'=>$areas,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'NJSRAR','found_divisor'=>$areas*1.1,'found_divider'=>0,'report_hash'=>$report_hash],
            
                    ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NSMAR','found_divisor'=>$areaC17,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'NJSMAR','found_divisor'=>$areaC17*1.1,'found_divider'=>0,'report_hash'=>$report_hash],


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
            case 'twelveYearCon':

                $areaC10 = $event->sheet->getCell("C10")->getValue();
                $areaC17 = $event->sheet->getCell("C17")->getValue();
                $areas = $areaC10 - $areaC17;

                $arr = [

                    ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNSRAR','found_divisor'=>$areas,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJSRAR','found_divisor'=>$areas*1.1,'found_divider'=>0,'report_hash'=>$report_hash],
            
                    ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNSMAR','found_divisor'=>$areaC17,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJSMAR','found_divisor'=>$areaC17*1.1,'found_divider'=>0,'report_hash'=>$report_hash],


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