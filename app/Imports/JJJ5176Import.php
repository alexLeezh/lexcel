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


class JJJ5176Import implements  WithEvents
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

        $books = $event->sheet->getCell("D12")->getValue();

        $JSMAR = $event->sheet->getCell("D7")->getValue();

        $JSMR = $event->sheet->getCell("D19")->getValue();

        $JHIR = $event->sheet->getCell("D17")->getValue();


        $arr = [
            ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'modern','found_ind'=>'JSBR','found_divisor'=>$books,'found_divider'=>0,'report_hash'=>$report_hash],

            ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'balance','found_ind'=>'JSMAR','found_divisor'=>$JSMAR,'found_divider'=>0,'report_hash'=>$report_hash],
            ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'balance','found_ind'=>'JSMR','found_divisor'=>$JSMR*10000,'found_divider'=>0,'report_hash'=>$report_hash],
            ['school_type'=>'juniorMiddleSchool','school'=>$school,'report_type'=>'balance','found_ind'=>'JHIR','found_divisor'=>$JHIR,'found_divider'=>0,'report_hash'=>$report_hash],


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