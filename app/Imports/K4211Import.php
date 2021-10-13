<?php
namespace App\Imports;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use App\Models\PreSheetData;

class K4211Import implements  WithEvents  
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
                $jteachers = $event->sheet->getCell("G9")->getValue();
                $bteachers = $event->sheet->getCell("F9")->getValue();
                $yteachers = $event->sheet->getCell("E9")->getValue();
                
                //report_type found_ind found_divisor found_divider
                $preSheetData->school_type = $school_type;
                $preSheetData->school = $school;
                $preSheetData->report_hash = $report_hash;

                $preSheetData->report_type = 'modern';
                $preSheetData->found_ind = 'KSTR';
                $preSheetData->found_divisor = $jteachers+$bteachers+$yteachers;//专科以上教师
                $preSheetData->save();

                break;
            case 'primarySchool':
                
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