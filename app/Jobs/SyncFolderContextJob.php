<?php

namespace App\Jobs;

use App\Models\Folder;
use App\Services\FolderBrainService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncFolderContextJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function __construct(public Folder $folder) {}

    /**
     * Build and cache the folder's AI context string.
     */
    public function handle(FolderBrainService $brainService): void
    {
        $contextText = $brainService->buildSummaryText($this->folder);

        $this->folder->update(['context_cache' => $contextText]);
    }
}
