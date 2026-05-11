<?php

namespace App\Livewire;

use App\Jobs\AnalyzeArticleJob;
use App\Models\Article;
use App\Services\CitationFormatter;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Detail Artikel')]
class ArticleDetail extends Component
{
    public Article $article;

    public string $citationStyle = 'apa';

    public string $referenceStyle = 'apa';

    public bool $isGenerating = false;

    public string $generateError = '';

    public function mount(Article $article): void
    {
        if ($article->user_id !== Auth::id()) {
            abort(403);
        }

        $this->article = $article->load('ktiType');
    }

    public function reanalyze(): void
    {
        if ($this->article->status === 'failed') {
            $this->article->update(['status' => 'pending']);
            AnalyzeArticleJob::dispatch($this->article);
            $this->article->refresh();
        }
    }

    /**
     * Generate reference (citation + bibliography) via Gemini AI.
     */
    public function generateReference(): void
    {
        $this->generateError = '';
        $this->isGenerating = true;

        try {
            $geminiService = app(GeminiService::class);

            $jsonData = $this->article->analysis_results ?? [];

            $result = $geminiService->generateReference($jsonData, $this->referenceStyle);

            $this->article->update([
                'citation_output' => $result['citation'],
                'bibliography_output' => $result['bibliography'],
            ]);

            $this->article->refresh();
        } catch (\Exception $e) {
            $this->generateError = 'Gagal generate referensi: '.$e->getMessage();
        } finally {
            $this->isGenerating = false;
        }
    }

    /**
     * Short in-text citation for the selected style (local formatter fallback).
     */
    public function getInTextCitationProperty(): string
    {
        return CitationFormatter::inText($this->article, $this->citationStyle);
    }

    /**
     * Full bibliography entry for the selected style (local formatter fallback).
     */
    public function getBibliographyProperty(): string
    {
        return CitationFormatter::bibliography($this->article, $this->citationStyle);
    }

    /**
     * Indicates whether any citation metadata is missing (so UI can warn the user).
     */
    public function getHasMissingMetadataProperty(): bool
    {
        $fields = CitationFormatter::extractFields($this->article);

        return ! ($fields['hasAuthor'] && $fields['hasYear'] && $fields['hasTitle']);
    }

    /**
     * Get dynamic analysis columns from KtiType template.
     *
     * @return array<string, mixed>
     */
    public function getDynamicColumnsProperty(): array
    {
        $results = $this->article->analysis_results ?? [];
        $templateColumns = $this->article->ktiType->columns ?? [];

        $dynamicData = [];
        foreach ($templateColumns as $column) {
            $dynamicData[$column] = $results[$column] ?? null;
        }

        return $dynamicData;
    }

    public function render()
    {
        return view('livewire.article-detail', [
            'article' => $this->article,
            'inTextCitation' => $this->inTextCitation,
            'bibliography' => $this->bibliography,
            'hasMissingMetadata' => $this->hasMissingMetadata,
            'citationStyles' => CitationFormatter::STYLES,
            'dynamicColumns' => $this->dynamicColumns,
        ]);
    }
}
