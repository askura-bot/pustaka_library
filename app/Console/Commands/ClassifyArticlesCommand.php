<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\GeminiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ClassifyArticlesCommand extends Command
{
    protected $signature = 'library:classify-articles';

    protected $description = 'Classify uncategorized articles using Gemini AI (batch)';

    private const CATEGORIES = [
        'Sains & Teknologi',
        'Kesehatan & Kedokteran',
        'Ekonomi, Bisnis & Akuntansi',
        'Sosial & Humaniora',
        'Hukum & Politik',
        'Pendidikan & Bahasa',
        'Pertanian, Lingkungan & Logistik',
        'Seni, Desain & Media',
        'Multidisiplin / Umum',
    ];

    public function handle(GeminiService $geminiService): int
    {
        $articles = Article::whereNull('category')
            ->where('status', 'completed')
            ->whereNotNull('analysis_results')
            ->get();

        if ($articles->isEmpty()) {
            $this->info('✓ Semua artikel sudah memiliki kategori.');

            return self::SUCCESS;
        }

        $this->info("Ditemukan {$articles->count()} artikel tanpa kategori.");
        $bar = $this->output->createProgressBar($articles->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($articles as $article) {
            try {
                $category = $this->classifyArticle($geminiService, $article);

                if ($category) {
                    $article->update(['category' => $category]);
                    $success++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->warn("  ⚠ Artikel #{$article->id}: ".$e->getMessage());
                $failed++;

                // Rate limit — wait before next request
                if (str_contains($e->getMessage(), 'RATE_LIMIT')) {
                    $this->info('  ⏳ Rate limited, menunggu 60 detik...');
                    sleep(60);
                } else {
                    sleep(2);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✓ Selesai! Berhasil: {$success}, Gagal: {$failed}");

        return self::SUCCESS;
    }

    /**
     * Classify a single article by sending its title/abstract to Gemini.
     */
    private function classifyArticle(GeminiService $geminiService, Article $article): ?string
    {
        $results = $article->analysis_results ?? [];

        // Build a short text for classification
        $title = $article->title ?? ($results['title'] ?? $article->file_name);
        $abstract = $results['abstract'] ?? '';
        $keywords = is_array($article->keywords) ? implode(', ', $article->keywords) : '';

        $text = "Judul: {$title}";
        if ($abstract) {
            $text .= "\nAbstrak: {$abstract}";
        }
        if ($keywords) {
            $text .= "\nKata Kunci: {$keywords}";
        }

        $categoriesList = implode(', ', array_map(fn ($c) => "'{$c}'", self::CATEGORIES));

        $prompt = 'Klasifikasikan dokumen berikut ke dalam SATU kategori bidang ilmu. '.
                  "Pilih HANYA dari daftar ini: {$categoriesList}. ".
                  "Jawab dengan nama kategori saja, tanpa penjelasan.\n\n{$text}";

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'.
               config('services.gemini.model_chat', 'gemini-2.5-flash').
               ':generateContent?key='.config('services.gemini.api_key');

        $response = Http::timeout(30)->post($url, [
            'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => 0.1, 'maxOutputTokens' => 50],
        ]);

        if ($response->status() === 429) {
            throw new \Exception('RATE_LIMIT_EXCEEDED');
        }

        if ($response->failed()) {
            throw new \Exception('API Error: '.$response->status());
        }

        $result = $response->json();
        $text = trim($result['candidates'][0]['content']['parts'][0]['text'] ?? '');

        // Validate against allowed categories
        foreach (self::CATEGORIES as $cat) {
            if (str_contains($text, $cat)) {
                return $cat;
            }
        }

        // If exact match not found, return raw (AI might have slight variation)
        return $text ?: null;
    }
}
