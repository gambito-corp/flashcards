<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fileContent;
    protected $path;
    protected $diskName;

    public function __construct($fileContent, $path, $diskName = 'new_s3')
    {
        $this->fileContent = $fileContent;
        $this->path = $path;
        $this->diskName = $diskName;
    }

    public function handle(): void
    {
        Storage::disk($this->diskName)->put($this->path, $this->fileContent);
    }
}
