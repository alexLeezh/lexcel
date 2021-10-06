<?php

namespace App\Listeners;

use App\Events\RecordEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use app\Providers\RecordServiceProvider;

class RecordListener
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
     * @param  \App\Events\RecordEvent  $event
     * @return void
     */
    public function handle(RecordEvent $event)
    {
        $recordService = new RecordServiceProvider();
        $recordService->dependencyDel($event->entities);
    }
}
