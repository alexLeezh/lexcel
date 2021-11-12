<?php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithConditionalSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Illuminate\Support\Facades\Log;
use App\Imports\EsImport;
use App\Imports\K211Import;
use App\Imports\K411Import;
use App\Imports\K4211Import;

use App\Imports\K212Import;
use App\Imports\K312Import;
use App\Imports\K412Import;
use App\Imports\K422Import;
use App\Imports\K423Import;
use App\Imports\K531Import;

use App\Imports\K213Import;
use App\Imports\K313Import;
use App\Imports\K424Import;

use App\Imports\K314Import;
use App\Imports\K522Import;

use App\Imports\Z311Import;
use App\Imports\Z411Import;
use App\Imports\Z421Import;
use App\Imports\Z521Import;

use App\Imports\K315Import;
use App\Imports\K413Import;

use App\Imports\JJ1001Import;
use App\Imports\JJ4148Import;
use App\Imports\JJ2105Import;
use App\Imports\JJ4159Import;

use App\Imports\JJP1001Import;
use App\Imports\JJP3112Import;
use App\Imports\JJP4155Import;
use App\Imports\JJP2106Import;
use App\Imports\JJP4153Import;
use App\Imports\JJP5176Import;
use App\Imports\JJ1102Import;
use App\Imports\JJ5170Import;

use App\Imports\JJJ1001Import;
use App\Imports\JJJ3115Import;
use App\Imports\JJJ4149Import;
use App\Imports\JJJ4156Import;
use App\Imports\JJJ2107Import;
use App\Imports\JJJ4153Import;
use App\Imports\JJJ5176Import;
use App\Imports\JJJ4068Import;
use App\Imports\JJJ5170Import;

use App\Imports\JJH1001Import;
use App\Imports\JJH3118Import;
use App\Imports\JJH4149Import;
use App\Imports\JJH4156Import;
use App\Imports\JJH5176Import;

use App\Imports\JJS1001Import;
use App\Imports\JJS3120Import;
use App\Imports\JJS4150Import;

use App\Imports\JJN1001Import;
use App\Imports\JJN4155Import;
use App\Imports\JJN3112Import;
use App\Imports\JJN4156Import;
use App\Imports\JJN3115Import;
use App\Imports\JJN4149Import;
use App\Imports\JJN5176Import;
use App\Imports\JJN5170Import;
use App\Imports\JJN4153Import;


use App\Imports\JJSV1001Import;
use App\Imports\JJSV3221Import;
use App\Imports\JJSV4251Import;
use App\Imports\JJSV4261Import;
use App\Imports\JJSV5377Import;

use App\Imports\JJT1001Import;
use App\Imports\JJT1102Import;
use App\Imports\JJT4155Import;
use App\Imports\JJT3112Import;
use App\Imports\JJT4156Import;
use App\Imports\JJT3115Import;
use App\Imports\JJT5176Import;
use App\Imports\JJT5170Import;

class SchoolImport implements WithMultipleSheets ,SkipsUnknownSheets
{
    use WithConditionalSheets;
    private $importData;
    public function __construct(array $importData)
    {
        $this->importData = $importData;
        Log::info($importData);
    }

    public function conditionalSheets(): array
    {
        return [
            '教基1001' => new EsImport($this->importData),
            '教基4148' => new bs4148Import($this->importData),//幼儿园
            '教基2105' => new bs2105Import($this->importData),//幼儿园
            '教基4159' => new bs4159Import($this->importData),//幼儿园

            '教基3112' => new bs3112Import($this->importData),//小学\九年一贯制学校\十二年一贯制学校
            '教基4155' => new bs4155Import($this->importData),//小学\九年一贯制学校\十二年一贯制学校
            '教基2106' => new bs2106Import($this->importData),//小学\九年一贯制学校\十二年一贯制学校
            '教基4153' => new bs4153Import($this->importData),//小学\初中\九年一贯制学校\十二年一贯制学校
            '教基5176' => new bs5176Import($this->importData),//小学\初中\高中\九年一贯制学校\十二年一贯制学校
            '教基5170' => new bs5170Import($this->importData),//小学\初中\九年一贯制学校\十二年一贯制学校

            '教基3115' => new bs3115Import($this->importData),//初中\九年一贯制学校\十二年一贯制学校
            '教基4149' => new bs4149Import($this->importData),//初中\高中\九年一贯制学校\十二年一贯制学校
            '教基4156' => new bs4156Import($this->importData),//初中\高中\九年一贯制学校\十二年一贯制学校
            '教基2107' => new bs2107Import($this->importData),//初中\九年一贯制学校\十二年一贯制学校

            '教基3118' => new bs3118Import($this->importData),//高中\十二年一贯制学校

            '教基3120' => new bs3120Import($this->importData),//其他特教学校
            '教基4150' => new bs4150Import($this->importData),//其他特教学校

            '教基3221' => new bs3221Import($this->importData),//中等技术学校\职业高中学校
            '教基4251' => new bs4251Import($this->importData),//中等技术学校\职业高中学校
            '教基4261' => new bs4261Import($this->importData),//中等技术学校\职业高中学校
            '教基5377' => new bs5377Import($this->importData),//中等技术学校\职业高中学校

            '教基1102续' => new bs1102Import($this->importData),//小学\初中\高中\九年一贯制学校\十二年一贯制学校

            // '基础基111' => new EsImport($this->importData),
            // '基础基211' => new K211Import($this->importData),
            // '基础基411' => new K411Import($this->importData),
            // '基础基4211' => new K4211Import($this->importData),

            // '基础基212' => new K212Import($this->importData),
            // '基础基312' => new K312Import($this->importData),
            // '基础基412' => new K412Import($this->importData),//小学，初中 ,高中
            // '基础基422' => new K422Import($this->importData),//小学，初中
            // '基础基423' => new K423Import($this->importData),
            // '基础基531' => new K531Import($this->importData),//小学，初中

            // '基础基213' => new K213Import($this->importData),
            // '基础基313' => new K313Import($this->importData),
            // '基础基424' => new K424Import($this->importData), //初中，高中

            // '基础基314' => new K314Import($this->importData),
            // '基础基522' => new K522Import($this->importData),
            
            // '中职基111' => new EsImport($this->importData),
            // '中职基311' => new Z311Import($this->importData),
            // '中职基411' => new Z411Import($this->importData),
            // '中职基421' => new Z421Import($this->importData),
            // '中职基521' => new Z521Import($this->importData),

            // '基础基315' => new K315Import($this->importData),
            // '基础基413' => new K413Import($this->importData),

            // '基础基112' => new K112Import($this->importData),
            // '基础基512' => new K512Import($this->importData),

            //2021.10.21 新模板逻辑
            '教基1001_幼儿园' => new JJ1001Import($this->importData),
            '教基4148_幼儿园' => new JJ4148Import($this->importData),
            '教基2105_幼儿园' => new JJ2105Import($this->importData),
            '教基4159_幼儿园' => new JJ4159Import($this->importData),

            //小学P
            '教基1001_小学' => new JJP1001Import($this->importData),
            '教基3112_小学' => new JJP3112Import($this->importData),
            '教基4155_小学' => new JJP4155Import($this->importData),
            '教基2106_小学' => new JJP2106Import($this->importData),
            '教基4153_小学' => new JJP4153Import($this->importData),
            '教基5176_小学' => new JJP5176Import($this->importData),
            // '教基4068_小学' => new JJ4068Import($this->importData),
            '教基5170_小学' => new JJ5170Import($this->importData),
            '教基1102续_小学' => new JJ1102Import($this->importData),

            //初中J
            '教基1001_初级中学' => new JJJ1001Import($this->importData),
            '教基3115_初级中学' => new JJJ3115Import($this->importData),
            '教基4149_初级中学' => new JJJ4149Import($this->importData),
            '教基4156_初级中学' => new JJJ4156Import($this->importData),
            '教基2107_初级中学' => new JJJ2107Import($this->importData),
            '教基4153_初级中学' => new JJJ4153Import($this->importData),
            '教基5176_初级中学' => new JJJ5176Import($this->importData),
            // '教基4068_初级中学' => new JJJ4068Import($this->importData),
            '教基5170_初级中学' => new JJJ5170Import($this->importData),
            '教基1102续_初级中学' => new JJJ1102Import($this->importData),

            //高中 H
            '教基1001_高级中学' => new JJH1001Import($this->importData),
            '教基3118_高级中学' => new JJH3118Import($this->importData),
            '教基4149_高级中学' => new JJH4149Import($this->importData),
            '教基4156_高级中学' => new JJH4156Import($this->importData),
            '教基5176_高级中学' => new JJH5176Import($this->importData),

            //特殊 S
            '教基1001_其他特教学校' => new JJS1001Import($this->importData),
            '教基3120_其他特教学校' => new JJS3120Import($this->importData),
            '教基4150_其他特教学校' => new JJS4150Import($this->importData),

            //初中 N
            '教基1001_九年一贯制学校' => new JJN1001Import($this->importData),
            '教基4155_九年一贯制学校' => new JJN4155Import($this->importData),
            '教基3112_九年一贯制学校' => new JJN3112Import($this->importData),
            '教基4156_九年一贯制学校' => new JJN4156Import($this->importData),
            '教基3115_九年一贯制学校' => new JJN3115Import($this->importData),
            '教基5176_九年一贯制学校' => new JJN5176Import($this->importData),
            '教基5170_九年一贯制学校' => new JJN5170Import($this->importData),
            '教基1102续_九年一贯制学校' => new JJN1102Import($this->importData),
            '教基2106_九年一贯制学校' => new JJN2106Import($this->importData),
            '教基4153_九年一贯制学校' => new JJN4153Import($this->importData),
            '教基4149_九年一贯制学校' => new JJN4149Import($this->importData),
            '教基2107_九年一贯制学校' => new JJN2107Import($this->importData),

            //中职 SV
            '教基1001_中等技术学校' => new JJSV1001Import($this->importData),
            '教基3221_中等技术学校' => new JJSV3221Import($this->importData),
            '教基4251_中等技术学校' => new JJSV4251Import($this->importData),
            '教基4261_中等技术学校' => new JJSV4261Import($this->importData),
            '教基5377_中等技术学校' => new JJSV5377Import($this->importData),

            '教基1001_职业高中学校' => new JJSV1001Import($this->importData),
            '教基3221_职业高中学校' => new JJSV3221Import($this->importData),
            '教基4251_职业高中学校' => new JJSV4251Import($this->importData),
            '教基4261_职业高中学校' => new JJSV4261Import($this->importData),
            '教基5377_职业高中学校' => new JJSV5377Import($this->importData),

            //十二年 小学和初中
            '教基1001_十二年一贯制学校' => new JJT1001Import($this->importData),
            '教基4155_十二年一贯制学校' => new JJT4155Import($this->importData),
            '教基3112_十二年一贯制学校' => new JJT3112Import($this->importData),
            '教基4156_十二年一贯制学校' => new JJT4156Import($this->importData),
            '教基3115_十二年一贯制学校' => new JJT3115Import($this->importData),
            '教基5176_十二年一贯制学校' => new JJT5176Import($this->importData),
            '教基5170_十二年一贯制学校' => new JJT5170Import($this->importData),
            '教基1102续_十二年一贯制学校' => new JJT1102Import($this->importData),
            '教基2106_十二年一贯制学校' => new JJT2106Import($this->importData),
            '教基4153_十二年一贯制学校' => new JJT4153Import($this->importData),
            '教基4149_十二年一贯制学校' => new JJT4149Import($this->importData),
            '教基2107_十二年一贯制学校' => new JJT2107Import($this->importData),
            '教基3118_十二年一贯制学校' => new JJT3118Import($this->importData),
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        Log::info("Sheet {$sheetName} was skipped");
    }
}