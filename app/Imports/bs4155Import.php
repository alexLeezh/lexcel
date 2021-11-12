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

class bs4155Import implements  WithEvents
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
                $teachers = $event->sheet->getCell("C6")->getValue();

                $teachersC9 = $event->sheet->getCell("C9")->getValue();
                $teachersC10 = $event->sheet->getCell("C10")->getValue();
                $teachersC11 = $event->sheet->getCell("C11")->getValue();
                $teachersPTR = $teachersC9 + $teachersC10 + $teachersC11;

                $teachersL6 = $event->sheet->getCell("L6")->getValue();
                $teachersN6 = $event->sheet->getCell("N6")->getValue();
                $teachersO6 = $event->sheet->getCell("O6")->getValue();
                $teachersP6 = $event->sheet->getCell("P6")->getValue();
                $teachersPHATR = $teachersL6+$teachersN6+$teachersO6+$teachersP6;

                $arr = [
                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'modern','found_ind'=>'PSTR','found_divisor'=>0,'found_divider'=>$teachers,'report_hash'=>$report_hash],
                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'modern','found_ind'=>'PTR','found_divisor'=>0,'found_divider'=>$teachers,'report_hash'=>$report_hash],

                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'modern','found_ind'=>'PTR','found_divisor'=>$teachersPTR,'found_divider'=>0,'report_hash'=>$report_hash],


                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'balance','found_ind'=>'PHETR','found_divisor'=>$teachersPTR*100,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'balance','found_ind'=>'PHATR','found_divisor'=>$teachersPHATR*100,'found_divider'=>0,'report_hash'=>$report_hash],

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
                $teachersC6 = $event->sheet->getCell("C6")->getValue();

                $teachersC9 = $event->sheet->getCell("C9")->getValue();
                $teachersC10 = $event->sheet->getCell("C10")->getValue();
                $teachersC11 = $event->sheet->getCell("C11")->getValue();
                $teachersC12 = $event->sheet->getCell("C12")->getValue();

                $teachersNHETR = $teachersC10+ $teachersC11 +$teachersC12;
                $teachersMNPTR = $teachersC10+ $teachersC11 +$teachersC9;//MNPTR MTPTR

                $teachersL6 = $event->sheet->getCell("L6")->getValue();
                $teachersN6 = $event->sheet->getCell("N6")->getValue();
                $teachersO6 = $event->sheet->getCell("O6")->getValue();
                $teachersP6 = $event->sheet->getCell("P6")->getValue();

                $teachersNHATR = $teachersL6 +$teachersN6 +$teachersO6 +$teachersP6;

                $arr = [

                    ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NHETR','found_divisor'=>$teachersNHETR*100,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NHATR','found_divisor'=>$teachersNHATR*100,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>'mnineYearCon','school'=>$school,'report_type'=>'modern','found_ind'=>'MNPSTR','found_divisor'=>0,'found_divider'=>$teachersC6,'report_hash'=>$report_hash],
                    ['school_type'=>'mnineYearCon','school'=>$school,'report_type'=>'modern','found_ind'=>'MNPTR','found_divisor'=>0,'found_divider'=>$teachersC6,'report_hash'=>$report_hash],
                    ['school_type'=>'mnineYearCon','school'=>$school,'report_type'=>'modern','found_ind'=>'MNPTR','found_divisor'=>$teachersMNPTR,'found_divider'=>0,'report_hash'=>$report_hash],


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

                $teachersC6 = $event->sheet->getCell("C6")->getValue();

                $teachersC9 = $event->sheet->getCell("C9")->getValue();

                $teachersC10 = $event->sheet->getCell("C10")->getValue();
                $teachersC11 = $event->sheet->getCell("C11")->getValue();
                $teachersC12 = $event->sheet->getCell("C12")->getValue();

                $teachersNHETR = $teachersC10+ $teachersC11 +$teachersC12;

                $teachersMTPTR = $teachersC10+ $teachersC11 +$teachersC9;

                $teachersL6 = $event->sheet->getCell("L6")->getValue();
                $teachersN6 = $event->sheet->getCell("N6")->getValue();
                $teachersO6 = $event->sheet->getCell("O6")->getValue();
                $teachersP6 = $event->sheet->getCell("P6")->getValue();

                $teachersNHATR = $teachersL6 +$teachersN6 +$teachersO6 +$teachersP6;

                $arr = [

                    ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNHETR','found_divisor'=>$teachersNHETR*100,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNHATR','found_divisor'=>$teachersNHATR*100,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTPSTR','found_divisor'=>0,'found_divider'=>$teachersC6,'report_hash'=>$report_hash],
                    ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTPTR','found_divisor'=>$teachersMTPTR,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTPTR','found_divisor'=>0,'found_divider'=>$teachersC6,'report_hash'=>$report_hash],


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