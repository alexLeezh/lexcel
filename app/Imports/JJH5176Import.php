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


class JJH5176Import implements  WithEvents
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

        $mD19 = $event->sheet->getCell("D19")->getValue();

        $preSheetData = new PreSheetData();
        //report_type found_ind found_divisor found_divider
        $preSheetData->school_type = 'highSchool';
        $preSheetData->school = $school;
        $preSheetData->report_hash = $report_hash;

        $preSheetData->report_type = 'modern';
        $preSheetData->found_ind = 'HSMR';
        $preSheetData->found_divisor = $mD19;
        $preSheetData->user_id = self::$user_id;
        $preSheetData->save();
    	
    }
}