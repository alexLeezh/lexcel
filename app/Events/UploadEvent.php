<?php

namespace App\Events;
use Illuminate\Support\Facades\Log;

class UploadEvent extends Event
{
	public $entities;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($eventData)
    {
        $this->entities = $eventData;
    }
}
