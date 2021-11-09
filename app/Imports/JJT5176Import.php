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


class JJT5176Import implements  WithEvents
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

        $areaD7 = $event->sheet->getCell("D7")->getValue();

        $areaD19 = $event->sheet->getCell("D19")->getValue();

        $areaD12 = $event->sheet->getCell("D12")->getValue();

        $areaD17 = $event->sheet->getCell("D17")->getValue();

        $arr = [

            ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNSMAR','found_divisor'=>$areaD7,'found_divider'=>0,'report_hash'=>$report_hash],
            ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJSMAR','found_divisor'=>$areaD7*1.1,'found_divider'=>0,'report_hash'=>$report_hash],
    
            ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNSMR','found_divisor'=>$areaD19*10000,'found_divider'=>0,'report_hash'=>$report_hash],
            ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJSMR','found_divisor'=>$areaD19*10000*1.1,'found_divider'=>0,'report_hash'=>$report_hash],

            ['school_type'=>'twelveYearCon','school'=>$school,'report_type'=>'balance','found_ind'=>'TNHIR','found_divisor'=>$areaD17*100,'found_divider'=>0,'report_hash'=>$report_hash],
            ['school_type'=>'twelveYearCon','school'=>$school.'_初中','report_type'=>'balance','found_ind'=>'TNJHIR','found_divisor'=>$areaD17*1.1*100,'found_divider'=>0,'report_hash'=>$report_hash],

            ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTPSBR','found_divisor'=>$areaD12,'found_divider'=>0,'report_hash'=>$report_hash],
            ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTJSBR','found_divisor'=>$areaD12,'found_divider'=>0,'report_hash'=>$report_hash],
            ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTHSMR','found_divisor'=>$areaD19*100,'found_divider'=>0,'report_hash'=>$report_hash],            

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