<?php

namespace App\Events;
use Illuminate\Support\Facades\Log;

class ExampleEvent extends Event
{
	public $entities;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->entities = $data;

        Log::info('ExampleEvent'.var_export($data,true));
    }
}
