<?php

namespace App\Listeners;

use App\Events\ExampleEvent;
use App\Events\UploadEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UploadListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\UploadEvent  $event
     * @return void
     */
    public function handle(UploadEvent $event)
    {
        //处理上传 1、生成文件，2、写入数据库
        Log::info('UploadListener'.var_export($event->entities,true));
        
    }
}
