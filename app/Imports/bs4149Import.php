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

class bs4149Import implements  WithEvents
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
                
                break;
            case 'primarySchool':
                
                break;
            case 'juniorMiddleSchool':
                $teachers = $event->sheet->getCell("D6")->getValue();

                $preSheetData = new PreSheetData();
                //report_type found_ind found_divisor found_divider
                $preSheetData->school_type = 'juniorMiddleSchool';
                $preSheetData->school = $school;
                $preSheetData->report_hash = $report_hash;

                $preSheetData->report_type = 'modern';
                $preSheetData->found_ind = 'JSTR';
                $preSheetData->found_divider = $teachers;
                $preSheetData->user_id = self::$user_id;
                $preSheetData->save();
                break;
            case 'highSchool':
                $teachers = $event->sheet->getCell("D6")->getValue();

                $arr = [

                    ['school_type'=>'highSchool','school'=>$school,'report_type'=>'modern','found_ind'=>'HSTR','found_divisor'=>0,'found_divider'=>$teachers,'report_hash'=>$report_hash],
                    ['school_type'=>'highSchool','school'=>$school,'report_type'=>'modern','found_ind'=>'HETR','found_divisor'=>0,'found_divider'=>$teachers,'report_hash'=>$report_hash],

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
                break;
            case 'secondaryVocationalSchool':
                
                break;
            case 'nineYearCon':
                $teacherD6 = $event->sheet->getCell("D6")->getValue();

                $arr = [

                    ['school_type'=>'mnineYearCon','school'=>$school,'report_type'=>'modern','found_ind'=>'MNJSTR','found_divisor'=>0,'found_divider'=>$teacherD6,'report_hash'=>$report_hash],      

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

                break;
            case 'twelveYearCon':

                $teacherD6 = $event->sheet->getCell("D6")->getValue();
                $arr = [


                    ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTJSTR','found_divisor'=>0,'found_divider'=>$teacherD6,'report_hash'=>$report_hash], 
                    ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTHSTR','found_divisor'=>0,'found_divider'=>$teacherD6,'report_hash'=>$report_hash], 
                    ['school_type'=>'mtwelveYearCon','school'=>$school.'_十二年一贯','report_type'=>'modern','found_ind'=>'MTHETR','found_divisor'=>0,'found_divider'=>$teacherD6,'report_hash'=>$report_hash],      

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
                break;
            default:

                break;
        }
    	
    }
}