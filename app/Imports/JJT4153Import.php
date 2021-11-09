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


class JJT4153Import implements  WithEvents
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


        $teachersC10 = $event->sheet->getCell("C10")->getValue();
        $teachersC8 = $event->sheet->getCell("C8")->getValue();
        $teachersC9 = $event->sheet->getCell("C9")->getValue();

        $teachersMTPHSTR = $teachersC10+$teachersC8+$teachersC9;//MTPHSTR   

        $teachersC17 = $event->sheet->getCell("C17")->getValue();
        $teachersC18 = $event->sheet->getCell("C18")->getValue();
        $teachersC19 = $event->sheet->getCell("C19")->getValue();

        $teachersMTJHSTR = $teachersC17+$teachersC18+$teachersC19;// MTJHSTR  

        $arr = [

            ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTPHSTR','found_divisor'=>$teachersMTPHSTR*100,'found_divider'=>0,'report_hash'=>$report_hash],
            ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTJHSTR','found_divisor'=>$teachersMTJHSTR*100,'found_divider'=>0,'report_hash'=>$report_hash],

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