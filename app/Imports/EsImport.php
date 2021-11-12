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

class EsImport implements WithEvents
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
    	$school_type = $event->sheet->getCell("C27")->getValue();
    	$school_type_code = '';
    	switch ($school_type) {
    		case '幼儿园':
    			$school_type_code = 'kindergarten';
    			break;
    		case '小学':
    			$school_type_code = 'primarySchool';
    			break;
    		case '初级中学':
    			$school_type_code = 'juniorMiddleSchool';
    			break;
    		case '高级中学':
    			$school_type_code = 'highSchool';
    			break;
    		case '职业高中学校':
    			$school_type_code = 'secondaryVocationalSchool';
    			break;
            case '中等技术学校':
                $school_type_code = 'secondaryVocationalSchool';
                break;
    		case '其他特教学校':
    			$school_type_code = 'specialSchool';
    			break;
    		case '九年一贯制学校':
    			$school_type_code = 'nineYearCon';
    			break;
            case '十二年一贯制学校':
                $school_type_code = 'twelveYearCon';
                break;
    		
    		default:
    			# code...
    			break;
    	}
    	app('session')->put('school_type',$school_type_code);
    	//该批次数据
    	$batch = date('Ymdhi',time()).md5($school);
    	app('session')->put('report_hash',$batch);
        Redis::set('report_hash', $batch);
    }
}