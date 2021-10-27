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


class JJP2106Import implements  WithEvents
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

        $class_num = $event->sheet->getCell("C5")->getValue();

        $class_numC6 = $event->sheet->getCell("C6")->getValue();
        $class_numC7 = $event->sheet->getCell("C7")->getValue();
        $class_numC8 = $event->sheet->getCell("C8")->getValue();
        $class_numC9 = $event->sheet->getCell("C9")->getValue();
        $class_nums = $class_numC6 +$class_numC7+$class_numC8+$class_numC9;

        $arr = [
            ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'modern','found_ind'=>'PFCR','found_divisor'=>0,'found_divider'=>$class_num,'report_hash'=>$report_hash],
            ['school_type'=>'primarySchool','school'=>$school,'report_type'=>'modern','found_ind'=>'PFCR','found_divisor'=>$class_nums,'found_divider'=>0,'report_hash'=>$report_hash],

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