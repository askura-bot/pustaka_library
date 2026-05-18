<?php

namespace App\Jobs;

use App\Models\Article;
use App\Services\GeminiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
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
     * Timeout job dalam detik (sinkron dengan HTTP timeout Gemini).
     */
    public $timeout = 300;

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

            $absolutePath = storage_path('app/private/'.$this->article->file_path);

            // 1. Upload File ke Gemini
            $fileUri = $geminiService->uploadFile($absolutePath, $mimeType);

            // Jeda 10 detik agar server Google selesai memproses dokumen (terutama PDF)
            sleep(10);

            // 2. Lakukan Analisis
            $columns = $this->article->ktiType->columns ?? [];
            $ktiTypeName = $this->article->ktiType->name ?? '';
            $resultJson = $geminiService->analyzeDocument($fileUri, $mimeType, $columns, $ktiTypeName);

            // 3. Extract citation metadata (title, author, year) with fallback keys
            $metadata = $this->extractCitationMetadata($resultJson ?? []);

            // 4. Extract keywords from results
            $keywords = $this->extractKeywords($resultJson ?? []);

            // 5. Extract category from results
            $category = $this->extractCategory($resultJson ?? []);

            // 6. Simpan hasil lengkap + kolom utama untuk sitasi + keywords + category
            $this->article->update([
                'status' => 'completed',
                'analysis_results' => $resultJson,
                'title' => $metadata['title'],
                'author' => $metadata['author'],
                'year' => $metadata['year'],
                'keywords' => $keywords,
                'category' => $category,
            ]);

        } catch (\Exception $e) {
            Log::error('AnalyzeArticleJob Error: '.$e->getMessage());

            // Re-throw exception agar masuk ke antrean retry atau ditangkap oleh failed()
            throw $e;
        } finally {
            if ($fileUri) {
                $geminiService->deleteFile($fileUri);
            }
        }
    }

    /**
     * Extract keywords from analysis results.
     *
     * @param  array<string, mixed>  $results
     * @return array<int, string>|null
     */
    protected function extractKeywords(array $results): ?array
    {
        $candidates = ['keywords', 'Keywords', 'kata_kunci', 'Kata Kunci'];

        foreach ($candidates as $key) {
            if (! array_key_exists($key, $results)) {
                continue;
            }

            $value = $results[$key];

            if (is_array($value)) {
                return array_values(array_filter($value, 'is_scalar'));
            }

            // If it's a comma-separated string
            if (is_string($value) && trim($value) !== '') {
                return array_map('trim', explode(',', $value));
            }
        }

        return null;
    }

    /**
     * Extract category from analysis results.
     *
     * @param  array<string, mixed>  $results
     */
    protected function extractCategory(array $results): ?string
    {
        $candidates = ['category', 'Category', 'kategori', 'Kategori'];

        foreach ($candidates as $key) {
            if (! array_key_exists($key, $results)) {
                continue;
            }

            $value = $results[$key];

            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
    }

    /**
     * Extract title, author, year from analysis results using multiple fallback keys.
     *
     * @param  array<string, mixed>  $results
     * @return array{title: ?string, author: ?string, year: ?string}
     */
    protected function extractCitationMetadata(array $results): array
    {
        return [
            'title' => $this->pickFirst($results, ['title', 'Title', 'judul', 'Judul']),
            'author' => $this->pickFirst($results, ['author', 'Author', 'penulis', 'Penulis', 'authors', 'Authors']),
            'year' => $this->pickFirst($results, ['year', 'Year', 'tahun', 'Tahun', 'publication_year', 'Publication Year']),
        ];
    }

    /**
     * Pick the first non-empty scalar value from an array using a list of candidate keys.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $keys
     */
    protected function pickFirst(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            $value = $data[$key];

            if (is_array($value)) {
                $value = implode(', ', array_filter($value, 'is_scalar'));
            }

            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
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
