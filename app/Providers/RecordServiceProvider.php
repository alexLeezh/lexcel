<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecordServiceProvider extends ServiceProvider 
{
    public function __construct()
    {
    }
    /**
     * 删除依赖表格
     * @param  数据
     * @return 
     */
    public function dependencyDel($data)
    {
        if (!$data['form_id']) {
            return false;
        }
        Log::info('RecordServiceProvider'.var_export($data,1));
        $deleted = DB::table('sheet_record')->delete(['form_id' => $data['form_id']]);
        return $deleted;
    }

    
}
