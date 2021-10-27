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


class JJN3112Import implements  WithEvents
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

        $students = $event->sheet->getCell("I6")->getValue();

        $arr = [

            ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NHETR','found_divisor'=>0,'found_divider'=>$students*100,'report_hash'=>$report_hash],
            ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NHBTR','found_divisor'=>0,'found_divider'=>$students*100,'report_hash'=>$report_hash],
            ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NHATR','found_divisor'=>0,'found_divider'=>$students*100,'report_hash'=>$report_hash],
            ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NSRAR','found_divisor'=>0,'found_divider'=>$students*100,'report_hash'=>$report_hash],
            ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NJSRAR','found_divisor'=>0,'found_divider'=>$students*1.1*100,'report_hash'=>$report_hash],
            ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NSMAR','found_divisor'=>0,'found_divider'=>$students*1.1*100,'report_hash'=>$report_hash],
            ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NJSMAR','found_divisor'=>0,'found_divider'=>$students*1.1*100,'report_hash'=>$report_hash],
            ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NSMR','found_divisor'=>0,'found_divider'=>$students*1.1*100,'report_hash'=>$report_hash],
            ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NJSMAR','found_divisor'=>0,'found_divider'=>$students*1.1*100,'report_hash'=>$report_hash],
            ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NSMR','found_divisor'=>0,'found_divider'=>$students*1.1*100,'report_hash'=>$report_hash],
            ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NJSMR','found_divisor'=>0,'found_divider'=>$students*1.1*100,'report_hash'=>$report_hash],

            ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NHIR','found_divisor'=>0,'found_divider'=>$students*1.1*100,'report_hash'=>$report_hash],
            ['school_type'=>'nineYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'NJHIR','found_divisor'=>0,'found_divider'=>$students*1.1*100,'report_hash'=>$report_hash],

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