<?php

namespace App\Livewire;

use App\Models\Article;
use App\Models\ChatHistory;
use App\Services\GeminiService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Ask AI — Research Assistant')]
class GlobalChat extends Component
{
    public string $message = '';

    public bool $isSending = false;

    public string $chatError = '';

    /**
     * Send a global chat message with keyword-based RAG.
     */
    public function sendMessage(): void
    {
        $this->chatError = '';

        $this->validate([
            'message' => 'required|string|min:2|max:2000',
        ], [
            'message.required' => 'Tulis pertanyaanmu dulu.',
            'message.min' => 'Pertanyaan terlalu pendek.',
            'message.max' => 'Pertanyaan terlalu panjang (maks 2000 karakter).',
        ]);

        $this->isSending = true;
        $userMessage = trim($this->message);
        $this->message = '';

        try {
            $geminiService = app(GeminiService::class);

            // 1. Keyword retrieval: find top 3 relevant articles
            $relevantArticles = $this->retrieveRelevantArticles($userMessage);

            // 2. Get previous global chat history
            $previousChats = ChatHistory::where('user_id', Auth::id())
                ->whereNull('article_id')
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

            // 3. Build context from relevant articles
            $articlesContext = $relevantArticles->map(fn (Article $article) => [
                'title' => $article->title ?? $article->file_name,
                'author' => $article->author,
                'so_what' => $article->analysis_results['so_what'] ?? null,
                'abstract' => $article->analysis_results['abstract'] ?? null,
                'keywords' => $article->keywords,
            ])->toArray();

            // 4. Send to Gemini with context
            $response = $geminiService->globalChat($userMessage, $articlesContext, $previousChats);

            // 5. Save to database (article_id = null for global chat)
            ChatHistory::create([
                'user_id' => Auth::id(),
                'article_id' => null,
                'message' => $userMessage,
                'response' => $response,
                'metadata' => [
                    'model' => 'gemini-2.0-flash',
                    'sources' => $relevantArticles->pluck('title')->toArray(),
                    'source_count' => $relevantArticles->count(),
                ],
            ]);

            $this->dispatch('chat-updated');
        } catch (\Exception $e) {
            $this->chatError = 'Gagal mengirim pesan: '.$e->getMessage();
            $this->message = $userMessage;
        } finally {
            $this->isSending = false;
        }
    }

    /**
     * Retrieve top 3 most relevant articles based on keyword matching.
     *
     * @return Collection<int, Article>
     */
    protected function retrieveRelevantArticles(string $query): Collection
    {
        $searchTerm = mb_strtolower($query);

        // Split query into individual words for broader matching
        $words = array_filter(explode(' ', $searchTerm), fn ($w) => mb_strlen($w) >= 3);

        if (empty($words)) {
            $words = [$searchTerm];
        }

        return Article::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->where(function ($q) use ($words, $searchTerm) {
                // Match full query in keywords or title
                $q->whereRaw('keywords::text ILIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('LOWER(title) LIKE ?', ["%{$searchTerm}%"]);

                // Also match individual words
                foreach ($words as $word) {
                    $q->orWhereRaw('keywords::text ILIKE ?', ["%{$word}%"])
                        ->orWhereRaw('LOWER(title) LIKE ?', ["%{$word}%"])
                        ->orWhereRaw('analysis_results::text ILIKE ?', ["%{$word}%"]);
                }
            })
            ->orderByRaw('
                CASE
                    WHEN keywords::text ILIKE ? THEN 0
                    WHEN LOWER(title) LIKE ? THEN 1
                    ELSE 2
                END ASC
            ', ["%{$searchTerm}%", "%{$searchTerm}%"])
            ->take(3)
            ->get();
    }

    /**
     * Get global chat history.
     *
     * @return Collection<int, ChatHistory>
     */
    public function getChatHistoryProperty()
    {
        return ChatHistory::where('user_id', Auth::id())
            ->whereNull('article_id')
            ->oldest()
            ->get();
    }

    public function render()
    {
        return view('livewire.global-chat', [
            'chatHistory' => $this->chatHistory,
        ]);
    }
}
