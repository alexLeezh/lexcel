<?php

namespace App\Listeners;

use App\Events\DownLoadEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use app\Providers\RecordServiceProvider;
use Illuminate\Support\Facades\Log;

class DownLoadListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\DownLoadEvent  $event
     * @return void
     */
    public function handle(DownLoadEvent $event)
    {
        //保存下载文件路径
        Log::info($event->entities);
        DB::table('download_record')->delete();
        DB::table('download_record')->insert($event->entities);
    }
}
