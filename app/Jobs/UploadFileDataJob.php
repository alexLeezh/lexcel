<?php
namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use App\Service\UploadService;
class UploadFileDataJob extends Job 
{
    protected $uploadFileInfo;
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
        $uploadFileService->handleUploadData($this->uploadFileInfo);
        
    }
}
