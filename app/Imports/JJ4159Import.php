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


class JJ4159Import implements  WithEvents
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

        $teacherD6 = $event->sheet->getCell("D6")->getValue();
        $teacherE6 = $event->sheet->getCell("E6")->getValue();
        $teacherF6 = $event->sheet->getCell("F6")->getValue();
        $teacherG6 = $event->sheet->getCell("G6")->getValue();

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
    	
    }
}