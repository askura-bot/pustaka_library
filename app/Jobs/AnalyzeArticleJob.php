<?php

namespace App\Jobs;

use App\Models\Article;
use App\Services\GeminiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $article;

    /**
     * Tentukan berapa kali job ini boleh mencoba lagi (retries)
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        // Exponential backoff: tunggu 60 detik, lalu 120 detik
        return [60, 120];
    }

    /**
     * Execute the job.
     */
    public function handle(GeminiService $geminiService): void
    {
        $fileUri = null;
        try {
            // Ubah status ke processing
            $this->article->update(['status' => 'processing']);

            // Dapatkan mime type berdasarkan ekstensi
            $mimeType = $this->article->file_type === 'pdf' 
                ? 'application/pdf' 
                : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

            $absolutePath = storage_path('app/private/' . $this->article->file_path);

            // 1. Upload File ke Gemini
            $fileUri = $geminiService->uploadFile($absolutePath, $mimeType);

            // Jeda 10 detik agar server Google selesai memproses dokumen (terutama PDF)
            sleep(10);

            // 2. Lakukan Analisis
            $columns = $this->article->ktiType->columns ?? [];
            $resultJson = $geminiService->analyzeDocument($fileUri, $mimeType, $columns);

            // 3. Simpan hasil dan set completed
            $this->article->update([
                'status' => 'completed',
                'analysis_results' => $resultJson
            ]);

        } catch (\Exception $e) {
            Log::error('AnalyzeArticleJob Error: ' . $e->getMessage());
            
            // Re-throw exception agar masuk ke antrean retry atau ditangkap oleh failed()
            throw $e;
        } finally {
            if ($fileUri) {
                $geminiService->deleteFile($fileUri);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Jika semua retries gagal, ubah status artikel menjadi failed
        $this->article->update(['status' => 'failed']);
    }
}
