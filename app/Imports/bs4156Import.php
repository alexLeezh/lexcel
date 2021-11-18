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

class bs4156Import implements  WithEvents
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
                $teachersC6 = $event->sheet->getCell("C6")->getValue();

                $teachersC11 = $event->sheet->getCell("C11")->getValue();
                $teachersC10 = $event->sheet->getCell("C10")->getValue();
                $teachersJETR = $teachersC11 +$teachersC10;

                $teachersC12 = $event->sheet->getCell("C12")->getValue();
                // $teachersC13 = $event->sheet->getCell("C13")->getValue();
                $teachersJHETR = $teachersC12 +$teachersC10 +$teachersC11;


                $teachersU6 = $event->sheet->getCell("U6")->getValue();
                $teachersV6 = $event->sheet->getCell("V6")->getValue();
                $teachersW6 = $event->sheet->getCell("W6")->getValue();
                $teachersX6 = $event->sheet->getCell("X6")->getValue();
                $teachersJHATR = $teachersU6+$teachersV6+$teachersW6+$teachersX6;
                $arr = [
                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'modern','found_ind'=>'JETR','found_divisor'=>0,'found_divider'=>$teachersC6,'report_hash'=>$report_hash],
                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'modern','found_ind'=>'JETR','found_divisor'=>$teachersJETR,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'balance','found_ind'=>'JHETR','found_divisor'=>$teachersJHETR*100,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'balance','found_ind'=>'JHATR','found_divisor'=>$teachersJHATR*100,'found_divider'=>0,'report_hash'=>$report_hash],

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
                $teacherC10 = $event->sheet->getCell("C10")->getValue();
                $teacherC11 = $event->sheet->getCell("C11")->getValue();

                $preSheetData = new PreSheetData();
                //report_type found_ind found_divisor found_divider
                $preSheetData->school_type = 'highSchool';
                $preSheetData->school = $school;
                $preSheetData->report_hash = $report_hash;

                $preSheetData->report_type = 'modern';
                $preSheetData->found_ind = 'HETR';
                $preSheetData->found_divisor = $teacherC10+$teacherC11;
                $preSheetData->user_id = self::$user_id;
                $preSheetData->save();
                break;
            case 'secondaryVocationalSchool':
                
                break;
            case 'nineYearCon':
                $teacherC6 = $event->sheet->getCell("C6")->getValue();

                $teacherC11 = $event->sheet->getCell("C11")->getValue();
                $teacherC10 = $event->sheet->getCell("C10")->getValue();
                $teacherMNJETR = $teacherC11 + $teacherC10;

                $teacherC12 = $event->sheet->getCell("C12")->getValue();
                $teacherC13 = $event->sheet->getCell("C13")->getValue();

                $teacherNJHETR = $teacherC12 + $teacherC13;

                $teacherU6 = $event->sheet->getCell("U6")->getValue();
                $teacherV6 = $event->sheet->getCell("V6")->getValue();
                $teacherW6 = $event->sheet->getCell("W6")->getValue();
                $teacherX6 = $event->sheet->getCell("X6")->getValue();

                $teacherNJHATR = $teacherU6 + $teacherV6 + $teacherW6 + $teacherX6;
                $arr = [

                    ['school_type'=>'nineYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'NJHETR','found_divisor'=>$teacherNJHETR*100,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>'nineYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'NJHATR','found_divisor'=>$teacherNJHATR*100,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>'mnineYearCon','school'=>$school,'report_type'=>'modern','found_ind'=>'MNJETR','found_divisor'=>0,'found_divider'=>$teacherC6,'report_hash'=>$report_hash],
                    ['school_type'=>'mnineYearCon','school'=>$school,'report_type'=>'modern','found_ind'=>'MNJETR','found_divisor'=>$teacherMNJETR,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>'mnineYearCon','school'=>$school,'report_type'=>'modern','found_ind'=>'MNJSTR','found_divisor'=>0,'found_divider'=>$teacherC6,'report_hash'=>$report_hash],


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

                $teacherC6 = $event->sheet->getCell("C6")->getValue();

                $teacherC10 = $event->sheet->getCell("C10")->getValue();
                $teacherC11 = $event->sheet->getCell("C11")->getValue();
                $teacherMTJETR = $teacherC10 + $teacherC11;//MTJETR MTHETR

                $teacherC12 = $event->sheet->getCell("C12")->getValue();
                $teacherC13 = $event->sheet->getCell("C13")->getValue();

                $teacherNJHETR = $teacherC12 + $teacherC13;

                $teacherU6 = $event->sheet->getCell("U6")->getValue();
                $teacherV6 = $event->sheet->getCell("V6")->getValue();
                $teacherW6 = $event->sheet->getCell("W6")->getValue();
                $teacherX6 = $event->sheet->getCell("X6")->getValue();

                $teacherNJHATR = $teacherU6 + $teacherV6 + $teacherW6 + $teacherX6;
                $arr = [

                    ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJHETR','found_divisor'=>$teacherNJHETR*100,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJHATR','found_divisor'=>$teacherNJHATR*100,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTJETR','found_divisor'=>$teacherMTJETR,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTHETR','found_divisor'=>$teacherMTJETR,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTJETR','found_divisor'=>0,'found_divider'=>$teacherC6,'report_hash'=>$report_hash],


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