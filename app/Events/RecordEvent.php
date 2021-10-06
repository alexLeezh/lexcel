<?php

namespace App\Events;

class RecordEvent extends Event
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
    }
}
