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

class bs3115Import implements  WithEvents
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
                
                break;
            case 'juniorMiddleSchool':
                $students = $event->sheet->getCell("E6")->getValue();
                $arr = [

                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'modern','found_ind'=>'JSTR','found_divisor'=>$students,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'modern','found_ind'=>'JHSTR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'modern','found_ind'=>'JSBR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],

                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'balance','found_ind'=>'JHETR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'balance','found_ind'=>'JHBTR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'balance','found_ind'=>'JHATR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'balance','found_ind'=>'JSRAR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'balance','found_ind'=>'JSMAR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'balance','found_ind'=>'JSMR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'balance','found_ind'=>'JHIR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],


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
                $students = $event->sheet->getCell("E6")->getValue();

                $arr = [

                    ['school_type'=>'nineYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'NJHETR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'NJHBTR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'NJHATR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],

                    // ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NSRAR','found_divisor'=>0,'found_divider'=>$students*1.1,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'NJSRAR','found_divisor'=>0,'found_divider'=>$students*1.1,'report_hash'=>$report_hash],
                    
                    // ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NSMAR','found_divisor'=>0,'found_divider'=>$students*1.1,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'NJSMAR','found_divisor'=>0,'found_divider'=>$students*1.1,'report_hash'=>$report_hash],

                    // ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NSMR','found_divisor'=>0,'found_divider'=>$students*1.1,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'NJSMR','found_divisor'=>0,'found_divider'=>$students*1.1,'report_hash'=>$report_hash],
                    // ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NHIR','found_divisor'=>0,'found_divider'=>$students*1.1,'report_hash'=>$report_hash],
                    ['school_type'=>'nineYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'NJHIR','found_divisor'=>0,'found_divider'=>$students*1.1,'report_hash'=>$report_hash],

                    ['school_type'=>'mnineYearCon','school'=>$school,'report_type'=>'modern','found_ind'=>'MNJSTR','found_divisor'=>$students,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'mnineYearCon','school'=>$school,'report_type'=>'modern','found_ind'=>'MNJHSTR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'mnineYearCon','school'=>$school,'report_type'=>'modern','found_ind'=>'MNJSBR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],


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

                $students = $event->sheet->getCell("E6")->getValue();

                $arr = [

                    ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJHETR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJHBTR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJHATR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],

                    ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNSRAR','found_divisor'=>0,'found_divider'=>$students*1.1,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJSRAR','found_divisor'=>0,'found_divider'=>$students*1.1,'report_hash'=>$report_hash],
                    
                    ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNSMAR','found_divisor'=>0,'found_divider'=>$students*1.1,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJSMAR','found_divisor'=>0,'found_divider'=>$students*1.1,'report_hash'=>$report_hash],

                    ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNSMR','found_divisor'=>0,'found_divider'=>$students*1.1,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJSMR','found_divisor'=>0,'found_divider'=>$students*1.1,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNHIR','found_divisor'=>0,'found_divider'=>$students*1.1,'report_hash'=>$report_hash],
                    ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJHIR','found_divisor'=>0,'found_divider'=>$students*1.1,'report_hash'=>$report_hash],

                    ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTJSTR','found_divisor'=>$students,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTJHSTR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],
                    ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTJSBR','found_divisor'=>0,'found_divider'=>$students,'report_hash'=>$report_hash],


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