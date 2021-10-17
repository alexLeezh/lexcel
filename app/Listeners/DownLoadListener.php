<?php

namespace App\Listeners;

use App\Events\DownLoadEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use app\Providers\RecordServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
        $user =  Auth::getUser();
        //保存下载文件路径
        Log::info($event->entities);
        // DB::table('download_record')->delete();
        DB::delete('delete from download_record where user_id = '.$user->id);
        DB::delete('delete from form_record where user_id = '.$user->id);
        DB::delete('delete from pre_sheet_data where user_id = '.$user->id);
        DB::delete('delete from sheet_record where user_id = '.$user->id);
        
        DB::table('download_record')->insert($event->entities);
        
    }
}
