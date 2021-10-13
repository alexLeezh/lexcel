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


class K212Import implements  WithEvents
{

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
        $preSheetData = new PreSheetData();
        switch ($school_type) {
            case 'kindergarten':
                
                break;
            case 'primarySchool':
                $classUn401 = $event->sheet->getCell("E8")->getValue();
                $classUn402 = $event->sheet->getCell("E9")->getValue();
                $classUn403 = $event->sheet->getCell("E10")->getValue();
                $classUn404 = $event->sheet->getCell("E11")->getValue();
                $classUn40Total = $classUn401 + $classUn402 + $classUn403 +$classUn404;
                $classTotal = $event->sheet->getCell("E6")->getValue();

                $arr = [
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'modern','found_ind'=>'PFCR','found_divisor'=>$classUn40Total,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'modern','found_ind'=>'PFCR','found_divisor'=>0,'found_divider'=>$classTotal,'report_hash'=>$report_hash]
                ];

                foreach ($arr as $key => $value) {
                    Log::info($value);
                    $preSheetData = new PreSheetData();
                    //report_type found_ind found_divisor found_divider
                    $preSheetData->school_type = $value['school_type'];
                    $preSheetData->school = $value['school'];
                    $preSheetData->report_hash = $value['report_hash'];

                    $preSheetData->report_type = $value['report_type'];
                    $preSheetData->found_ind = $value['found_ind'];
                    $preSheetData->found_divisor = $value['found_divisor'];
                    $preSheetData->found_divider = $value['found_divider'];
                    $preSheetData->save();
                }
                
                break;
            case 'juniorMiddleSchool':
                
                break;
            case 'highSchool':
                
                break;
            case 'secondaryVocationalSchool':
                
                break;
            
            default:

                break;
        }
    	
    }
}