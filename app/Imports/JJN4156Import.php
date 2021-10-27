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


class JJN4156Import implements  WithEvents
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

        $teacherC12 = $event->sheet->getCell("C12")->getValue();
        $teacherC13 = $event->sheet->getCell("C13")->getValue();

        $teacherNJHETR = $teacherC12 + $teacherC13;

        $teacherU6 = $event->sheet->getCell("U6")->getValue();
        $teacherV6 = $event->sheet->getCell("V6")->getValue();
        $teacherW6 = $event->sheet->getCell("W6")->getValue();
        $teacherX6 = $event->sheet->getCell("X6")->getValue();

        $teacherNJHATR = $teacherU6 + $teacherV6 + $teacherW6 + $teacherX6;
        $arr = [

            ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NJHETR','found_divisor'=>$teacherNJHETR,'found_divider'=>0,'report_hash'=>$report_hash],

            ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NJHATR','found_divisor'=>$teacherNJHATR,'found_divider'=>0,'report_hash'=>$report_hash],


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
    	
    }
}