<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\ExampleEvent' => [
            'App\Listeners\ExampleListener',
        ],
        'App\Events\OrderShipped' => [
            'App\Listeners\SendShipmentNotification',
        ],
        'App\Events\UploadEvent' => [
            'App\Listeners\UploadListener',
        ],
        'App\Events\RecordEvent' => [
            'App\Listeners\RecordListener',
        ],
        'App\Events\DownLoadEvent' => [
            'App\Listeners\DownLoadListener',
        ],
    ];
}
