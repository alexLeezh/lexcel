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

class bs3112Import implements  WithEvents
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
                $students = $event->sheet->getCell("I6")->getValue();
                $arr = [
                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'modern','found_ind'=>'PSTR','found_divisor'=>$students,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'modern','found_ind'=>'PHSTR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'modern','found_ind'=>'PSBR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],

                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'balance','found_ind'=>'PHETR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'balance','found_ind'=>'PHBTR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'balance','found_ind'=>'PHATR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'balance','found_ind'=>'PSRAR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'balance','found_ind'=>'PSMAR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'balance','found_ind'=>'PSMR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'balance','found_ind'=>'PHIR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash]

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
                
                break;
            case 'highSchool':
                
                break;
            case 'secondaryVocationalSchool':
                
                break;
            case 'nineYearCon':
                $students = $event->sheet->getCell("I6")->getValue();

                $arr = [

                    ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NHETR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NHBTR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NHATR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NSRAR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'NJSRAR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NSMAR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'NJSMAR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NSMR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'NJSMR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],

                    ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NHIR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'NJHIR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],

                    ['school_type'=>'mnineYearCon','school'=>$school,'report_type'=>'modern','found_ind'=>'MNPSTR','found_divisor'=>$students,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'mnineYearCon','school'=>$school,'report_type'=>'modern','found_ind'=>'MNPHSTR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'mnineYearCon','school'=>$school,'report_type'=>'modern','found_ind'=>'MNPSBR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],


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

                $students = $event->sheet->getCell("I6")->getValue();

                $arr = [

                    ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNHETR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNHBTR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNHATR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNSRAR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJSRAR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNSMAR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJSMAR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNSMR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJSMR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],

                    ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNHIR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJHIR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],

                    ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTPSTR','found_divisor'=>$students,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTPHSTR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTPSBR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],

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