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

class K424Import implements  WithEvents
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
                $teacher1 = $event->sheet->getCell("D12")->getValue();//专科以上老师
                $teacher2 = $event->sheet->getCell("D13")->getValue();//专科以上老师
                $teacher3 = $event->sheet->getCell("D14")->getValue();//专科以上老师
                $teachers = $teacher1 + $teacher2 + $teacher3;

                //balance
                $bteachers = $teacher2 + $teacher3;

                $teacherV8 = $event->sheet->getCell("V8")->getValue();
                $teacherW8 = $event->sheet->getCell("W8")->getValue();
                $teacherX8 = $event->sheet->getCell("X8")->getValue();
                $teacherY8 = $event->sheet->getCell("Y8")->getValue();
                $arteachers = $teacherV8 + $teacherV8 + $teacherV8 +$teacherV8;

                $arr = [
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'modern','found_ind'=>'JETR','found_divisor'=>$teachers,'found_divider'=>0,'report_hash'=>$report_hash],

                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'JHETR','found_divisor'=>$bteachers,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'JHATR','found_divisor'=>$arteachers,'found_divider'=>0,'report_hash'=>$report_hash],

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
                $preSheetData = new PreSheetData();
                $teacher1 = $event->sheet->getCell("D20")->getValue();//专科以上老师
                $teacher2 = $event->sheet->getCell("D21")->getValue();//专科以上老师
                $teacher3 = $event->sheet->getCell("D22")->getValue();//专科以上老师
                $teachers = $teacher1 + $teacher2 + $teacher3;
                //report_type found_ind found_divisor found_divider
                $preSheetData->school_type = $school_type;
                $preSheetData->school = $school;
                $preSheetData->report_hash = $report_hash;

                $preSheetData->report_type = 'modern';
                $preSheetData->found_ind = 'HETR';
                $preSheetData->found_divisor = $teachers;//班级数
                $preSheetData->user_id = self::$user_id;
                $preSheetData->save();
                break;
            case 'secondaryVocationalSchool':
                
                break;
            case 'nineYearCon':
                $teacher2 = $event->sheet->getCell("D13")->getValue();//专科以上老师
                $teacher3 = $event->sheet->getCell("D14")->getValue();
                //balance
                $bteachers = $teacher2 + $teacher3;

                $teacherV8 = $event->sheet->getCell("V8")->getValue();
                $teacherW8 = $event->sheet->getCell("W8")->getValue();
                $teacherX8 = $event->sheet->getCell("X8")->getValue();
                $teacherY8 = $event->sheet->getCell("Y8")->getValue();
                $arteachers = $teacherV8 + $teacherV8 + $teacherV8 +$teacherV8;

                $arr = [

                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'NJHETR','found_divisor'=>$bteachers,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'NJHATR','found_divisor'=>$arteachers,'found_divider'=>0,'report_hash'=>$report_hash],

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