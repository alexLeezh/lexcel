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


class K423Import implements  WithEvents
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
        switch ($school_type) {
            case 'kindergarten':

                break;
            case 'primarySchool':
                $teachery = $event->sheet->getCell("D11")->getValue();
                $teacherb = $event->sheet->getCell("D12")->getValue();
                $teachers = $teachery + $teacherb;
                //balance
                $teacherc = $event->sheet->getCell("D13")->getValue();
                $bteachers = $teachery + $teacherb + $teacherc;

                $teacherM8 = $event->sheet->getCell("M8")->getValue();
                $teacherN8 = $event->sheet->getCell("N8")->getValue();
                $teacherP8 = $event->sheet->getCell("P8")->getValue();
                $teacherQ8 = $event->sheet->getCell("Q8")->getValue();

                $artteacher = $teacherM8 + $teacherN8 + $teacherP8 + $teacherQ8;

                $arr = [
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'modern','found_ind'=>'PTR','found_divisor'=>0,'found_divider'=>$teachers,'report_hash'=>$report_hash],
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'PHETR','found_divisor'=>$bteachers,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'PHATR','found_divisor'=>$artteacher,'found_divider'=>0,'report_hash'=>$report_hash],


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
            case 'nineYearCon':

                //balance
                $teacherb = $event->sheet->getCell("D12")->getValue();
                $teacherc = $event->sheet->getCell("D13")->getValue();
                $bteachers = $teacherb + $teacherc;

                $teacherM8 = $event->sheet->getCell("M8")->getValue();
                $teacherN8 = $event->sheet->getCell("N8")->getValue();
                $teacherP8 = $event->sheet->getCell("P8")->getValue();
                $teacherQ8 = $event->sheet->getCell("Q8")->getValue();

                $artteacher = $teacherM8 + $teacherN8 + $teacherP8 + $teacherQ8;

                $arr = [
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'NHETR','found_divisor'=>$bteachers,'found_divider'=>0,'report_hash'=>$report_hash],
                    ['school_type'=>$school_type,'school'=>$school,'report_type'=>'balance','found_ind'=>'NHATR','found_divisor'=>$artteacher,'found_divider'=>0,'report_hash'=>$report_hash],


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
            
            default:

                break;
        }
    	
    }
}