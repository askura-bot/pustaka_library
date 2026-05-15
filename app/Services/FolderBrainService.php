<?php

namespace App\Services;

use App\Models\Folder;

class FolderBrainService
{
    /**
     * Collect and compile all analysis data from articles in a folder.
     * This becomes the "brain" context for the folder chatbot.
     *
     * @return array<int, array{title: ?string, author: ?string, abstract: ?string, so_what: ?string, keywords: ?array, analysis: ?array}>
     */
    public function buildContext(Folder $folder): array
    {
        $articles = $folder->articles()
            ->where('status', 'completed')
            ->whereNotNull('analysis_results')
            ->get();

        return $articles->map(function ($article) {
            $results = $article->analysis_results ?? [];

            // Flatten nested legacy format if needed
            $flat = $this->flattenResults($results);

            return [
                'title' => $article->title ?? $article->file_name,
                'author' => $article->author,
                'abstract' => $flat['abstract'] ?? null,
                'so_what' => $flat['so_what'] ?? null,
                'conclusion' => $flat['conclusion'] ?? null,
                'keywords' => $article->keywords,
                'analysis' => $flat,
            ];
        })->toArray();
    }

    /**
     * Build a concise text summary for the folder context (for token efficiency).
     */
    public function buildSummaryText(Folder $folder): string
    {
        $context = $this->buildContext($folder);

        if (empty($context)) {
            return 'Folder ini belum memiliki artikel yang sudah dianalisis.';
        }

        $summary = "Folder \"{$folder->name}\" berisi ".count($context)." artikel:\n\n";

        foreach ($context as $i => $article) {
            $num = $i + 1;
            $summary .= "--- Artikel {$num} ---\n";
            $summary .= 'Judul: '.($article['title'] ?? 'Tanpa Judul')."\n";
            $summary .= 'Penulis: '.($article['author'] ?? 'Tidak diketahui')."\n";

            if ($article['abstract']) {
                $summary .= 'Abstrak: '.$article['abstract']."\n";
            }
            if ($article['so_what']) {
                $summary .= 'Esensi: '.$article['so_what']."\n";
            }
            if ($article['keywords']) {
                $summary .= 'Kata Kunci: '.implode(', ', $article['keywords'])."\n";
            }

            $summary .= "\n";
        }

        return $summary;
    }

    /**
     * Flatten nested analysis results (legacy format) into a single-level array.
     */
    protected function flattenResults(array $results): array
    {
        $flat = [];

        foreach ($results as $key => $value) {
            if (is_array($value) && ! array_is_list($value)) {
                foreach ($value as $childKey => $childValue) {
                    $flat[$childKey] = $childValue;
                }
            } else {
                $flat[$key] = $value;
            }
        }

        return $flat;
    }
}
