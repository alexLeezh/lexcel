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

class bs5176Import implements  WithEvents
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
        Log::info('bs5176Import');
        $school_type = app('session')->get('school_type');
        $school = app('session')->get('school');
        $report_hash = app('session')->get('report_hash');
        switch ($school_type) {
            case 'kindergarten':
                
                break;
            case 'primarySchool':
                $books = $event->sheet->getCell("D12")->getValue();
                $PSMAR = $event->sheet->getCell("D7")->getValue();
                $PSMR = $event->sheet->getCell("D19")->getValue();
                $PHIR = $event->sheet->getCell("D17")->getValue();
                Log::info('bs5176Import PSMR'.$PSMR);
                $arr = [
                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'modern','found_ind'=>'PSBR','found_divisor'=>$books,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'balance','found_ind'=>'PSMAR','found_divisor'=>$PSMAR,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'balance','found_ind'=>'PSMR','found_divisor'=>$PSMR*10000,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'balance','found_ind'=>'PHIR','found_divisor'=>$PHIR*100,'found_divider'=>0,'report_hash'=>$report_hash],


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
                $books = $event->sheet->getCell("D12")->getValue();
                $JSMAR = $event->sheet->getCell("D7")->getValue();
                $JSMR = $event->sheet->getCell("D19")->getValue();
                $JHIR = $event->sheet->getCell("D17")->getValue();

                $arr = [
                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'modern','found_ind'=>'JSBR','found_divisor'=>$books,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'balance','found_ind'=>'JSMAR','found_divisor'=>$JSMAR,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'balance','found_ind'=>'JSMR','found_divisor'=>$JSMR*10000,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'balance','found_ind'=>'JHIR','found_divisor'=>$JHIR*100,'found_divider'=>0,'report_hash'=>$report_hash],


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
                $mD19 = $event->sheet->getCell("D19")->getValue();

                $preSheetData = new PreSheetData();
                //report_type found_ind found_divisor found_divider
                $preSheetData->school_type = 'highSchool';
                $preSheetData->school = $school;
                $preSheetData->report_hash = $report_hash;

                $preSheetData->report_type = 'modern';
                $preSheetData->found_ind = 'HSMR';
                $preSheetData->found_divisor = $mD19*10000;
                $preSheetData->user_id = self::$user_id;
                $preSheetData->save();
                break;
            case 'secondaryVocationalSchool':
                
                break;
            case 'nineYearCon':
                $areaD7 = $event->sheet->getCell("D7")->getValue();
                $areaD19 = $event->sheet->getCell("D19")->getValue();
                $areaD17 = $event->sheet->getCell("D17")->getValue();
                $areaD12 = $event->sheet->getCell("D12")->getValue();

                $arr = [

                    ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NSMAR','found_divisor'=>$areaD7,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'NJSMAR','found_divisor'=>$areaD7*1.1,'found_divider'=>0,'report_hash'=>$report_hash],
            
                    ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NSMR','found_divisor'=>$areaD19*10000,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'NJSMR','found_divisor'=>$areaD19*10000*1.1,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NHIR','found_divisor'=>$areaD17*100,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'NJHIR','found_divisor'=>$areaD17*1.1*100,'found_divider'=>0,'report_hash'=>$report_hash],     

                    ['school_type'=>'mnineYearCon','school'=>$school,'report_type'=>'modern','found_ind'=>'MNPSBR','found_divisor'=>$areaD12,'found_divider'=>0,'report_hash'=>$report_hash],     
                    ['school_type'=>'mnineYearCon','school'=>$school,'report_type'=>'modern','found_ind'=>'MNJSBR','found_divisor'=>$areaD12,'found_divider'=>0,'report_hash'=>$report_hash],     

                ];
                Log::info('bs5176Import nineYearCon');
                Log::info($arr);
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

                $areaD7 = $event->sheet->getCell("D7")->getValue();
                $areaD19 = $event->sheet->getCell("D19")->getValue();
                $areaD12 = $event->sheet->getCell("D12")->getValue();
                $areaD17 = $event->sheet->getCell("D17")->getValue();

                $arr = [

                    ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNSMAR','found_divisor'=>$areaD7,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJSMAR','found_divisor'=>$areaD7*1.1,'found_divider'=>0,'report_hash'=>$report_hash],
            
                    ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNSMR','found_divisor'=>$areaD19*10000,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJSMR','found_divisor'=>$areaD19*10000*1.1,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNHIR','found_divisor'=>$areaD17*100,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJHIR','found_divisor'=>$areaD17*1.1*100,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTPSBR','found_divisor'=>$areaD12,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTJSBR','found_divisor'=>$areaD12,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTHSMR','found_divisor'=>$areaD19*10000,'found_divider'=>0,'report_hash'=>$report_hash],            

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