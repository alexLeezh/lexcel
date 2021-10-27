<?php
namespace App\Imports;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Models\PreSheetData;

class JJH1001Import implements  WithEvents
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
        $school = $event->sheet->getCell("C4")->getValue();
        app('session')->put('school',$school);
        $school_type = $event->sheet->getCell("C25")->getValue();
        $school_type_code = 'highSchool';
        app('session')->put('school_type',$school_type_code);
        //该批次数据
        $batch = date('Ymdhi',time()).md5($school);
        app('session')->put('report_hash',$batch);
        Redis::set('report_hash', $batch);
    }
}