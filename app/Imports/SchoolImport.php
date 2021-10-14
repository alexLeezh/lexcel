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

use App\Imports\K112Import;
use App\Imports\K512Import;


class SchoolImport implements WithMultipleSheets ,SkipsUnknownSheets
{
    use WithConditionalSheets;

    public function conditionalSheets(): array
    {
        return [
            '基础基111' => new EsImport(),
            '基础基211' => new K211Import(),
            '基础基411' => new K411Import(),
            '基础基4211' => new K4211Import(),

            '基础基212' => new K212Import(),
            '基础基312' => new K312Import(),
            '基础基412' => new K412Import(),//小学，初中 ,高中
            '基础基422' => new K422Import(),//小学，初中
            '基础基423' => new K423Import(),
            '基础基531' => new K531Import(),//小学，初中

            '基础基213' => new K213Import(),
            '基础基313' => new K313Import(),
            '基础基424' => new K424Import(), //初中，高中

            '基础基314' => new K314Import(),
            '基础基522' => new K522Import(),
            
            '中职基111' => new EsImport(),
            '中职基311' => new Z311Import(),
            '中职基411' => new Z411Import(),
            '中职基421' => new Z421Import(),
            '中职基521' => new Z521Import(),

            '基础基315' => new K315Import(),
            '基础基413' => new K413Import(),

            '基础基112' => new K112Import(),
            '基础基512' => new K512Import(),

        ];
    }

    public function onUnknownSheet($sheetName)
    {
        Log::info("Sheet {$sheetName} was skipped");
    }
}