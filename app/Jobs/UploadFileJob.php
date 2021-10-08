<?php
namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use App\Service\UploadService;
class UploadFileJob extends Job 
{
    protected $uploadFileInfo;
    protected $errorMsg;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($uploadFileInfo)
    {
        $this->uploadFileInfo = $uploadFileInfo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $uploadFileService = new UploadService();
        $uploadFileService->handleUploadFile($this->uploadFileInfo);
        Log::info('UploadFileJob'.var_export($this->uploadFileInfo,true));
    }
}
