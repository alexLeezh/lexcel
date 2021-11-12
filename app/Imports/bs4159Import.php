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

class bs4159Import implements  WithEvents
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
                $teacherD6 = $event->sheet->getCell("D9")->getValue();
                $teacherE6 = $event->sheet->getCell("E9")->getValue();
                $teacherF6 = $event->sheet->getCell("F9")->getValue();
                $teacherG6 = $event->sheet->getCell("G9")->getValue();

                $preSheetData = new PreSheetData();
                //report_type found_ind found_divisor found_divider
                $preSheetData->school_type = $school_type;
                $preSheetData->school = $school;
                $preSheetData->report_hash = $report_hash;

                $preSheetData->report_type = 'modern';
                $preSheetData->found_ind = 'KSTR';
                $preSheetData->found_divisor = $teacherD6+$teacherE6+$teacherF6+$teacherG6;
                $preSheetData->user_id = self::$user_id;
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