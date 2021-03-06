<?php

namespace App\Jobs;
use Illuminate\Support\Facades\Log;

class ExampleJob extends Job 
{
    protected $jobData;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($jobData)
    {
        $this->jobData = $jobData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //

        Log::info('ExampleJob'.var_export($this->jobData,true));

    }
}
