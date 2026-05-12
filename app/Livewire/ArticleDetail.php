<?php

namespace App\Livewire;

use App\Jobs\AnalyzeArticleJob;
use App\Models\Article;
use App\Models\ChatHistory;
use App\Services\CitationFormatter;
use App\Services\GeminiService;
use Illuminate\Database\Eloquent\Collection;
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

    // Chat properties
    public string $chatMessage = '';

    public bool $isSendingChat = false;

    public string $chatError = '';

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
     * Send a chat message and get AI response with article context.
     */
    public function sendMessage(): void
    {
        $this->chatError = '';

        $this->validate([
            'chatMessage' => 'required|string|min:2|max:2000',
        ], [
            'chatMessage.required' => 'Tulis pertanyaanmu dulu.',
            'chatMessage.min' => 'Pertanyaan terlalu pendek.',
            'chatMessage.max' => 'Pertanyaan terlalu panjang (maks 2000 karakter).',
        ]);

        $this->isSendingChat = true;
        $message = trim($this->chatMessage);
        $this->chatMessage = '';

        try {
            $geminiService = app(GeminiService::class);

            // Get previous chat history for context continuity
            $previousChats = ChatHistory::where('user_id', Auth::id())
                ->where('article_id', $this->article->id)
                ->latest()
                ->take(10)
                ->get()
                ->reverse()
                ->map(fn (ChatHistory $chat) => [
                    'message' => $chat->message,
                    'response' => $chat->response,
                ])
                ->values()
                ->toArray();

            // Send to Gemini with article context
            $analysisContext = $this->article->analysis_results ?? [];
            $response = $geminiService->chatWithContext($message, $analysisContext, $previousChats);

            // Save to database
            ChatHistory::create([
                'user_id' => Auth::id(),
                'article_id' => $this->article->id,
                'message' => $message,
                'response' => $response,
                'metadata' => [
                    'model' => 'gemini-2.0-flash',
                    'article_title' => $this->article->title ?? $this->article->file_name,
                ],
            ]);

            // Dispatch scroll event
            $this->dispatch('chat-updated');
        } catch (\Exception $e) {
            $this->chatError = 'Gagal mengirim pesan: '.$e->getMessage();
            // Restore the message so user doesn't lose it
            $this->chatMessage = $message;
        } finally {
            $this->isSendingChat = false;
        }
    }

    /**
     * Get chat history for this article.
     *
     * @return Collection<int, ChatHistory>
     */
    public function getChatHistoryProperty()
    {
        return ChatHistory::where('user_id', Auth::id())
            ->where('article_id', $this->article->id)
            ->oldest()
            ->get();
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
     * Indicates whether any citation metadata is missing.
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
            'chatHistory' => $this->chatHistory,
        ]);
    }
}
