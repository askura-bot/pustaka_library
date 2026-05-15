<?php

namespace App\Livewire;

use App\Jobs\AnalyzeArticleJob;
use App\Jobs\SyncFolderContextJob;
use App\Models\Article;
use App\Models\ChatHistory;
use App\Models\Folder;
use App\Models\KtiType;
use App\Services\FolderBrainService;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Folder')]
class FolderView extends Component
{
    use WithFileUploads;

    public Folder $folder;

    public bool $showAddArticleModal = false;

    public bool $showUploadModal = false;

    public bool $showChat = false;

    public string $search = '';

    // Upload properties
    public $file;

    public string $selectedKtiTypeId = '';

    // Chat properties
    public string $chatMessage = '';

    public bool $isSendingChat = false;

    public string $chatError = '';

    public function mount(Folder $folder): void
    {
        if ($folder->user_id !== Auth::id()) {
            abort(403);
        }

        $this->folder = $folder->load('articles.ktiType');
    }

    /**
     * Get articles in this folder.
     */
    public function getArticlesProperty()
    {
        return $this->folder->articles()->with('ktiType')->latest()->get();
    }

    /**
     * Get KTI types for upload dropdown.
     */
    public function getKtiTypesProperty()
    {
        return Auth::user()->ktiTypes()->get();
    }

    /**
     * Get articles NOT in this folder (for adding).
     */
    public function getAvailableArticlesProperty()
    {
        $existingIds = $this->folder->articles()->pluck('articles.id');

        $query = Auth::user()->articles()
            ->whereNotIn('id', $existingIds)
            ->where('status', 'completed');

        if (trim($this->search) !== '') {
            $term = mb_strtolower(trim($this->search));
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(file_name) LIKE ?', ["%{$term}%"]);
            });
        }

        return $query->latest()->take(20)->get();
    }

    /**
     * Upload a file directly into this folder.
     */
    public function uploadFile(): void
    {
        $this->validate([
            'selectedKtiTypeId' => 'required|exists:kti_types,id',
            'file' => 'required|mimes:pdf,docx|max:10240',
        ], [
            'selectedKtiTypeId.required' => 'Pilih Jenis KTI terlebih dahulu.',
            'file.required' => 'Pilih file yang ingin diunggah.',
            'file.mimes' => 'Hanya file PDF atau DOCX yang didukung.',
            'file.max' => 'Ukuran maksimal file adalah 10MB.',
        ]);

        $user = Auth::user();

        $ktiType = KtiType::where('id', $this->selectedKtiTypeId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $originalName = $this->file->getClientOriginalName();
        $extension = $this->file->getClientOriginalExtension();

        $path = $this->file->storeAs(
            'articles/'.$user->id,
            uniqid().'_'.time().'.'.$extension,
            'local'
        );

        $article = $user->articles()->create([
            'kti_type_id' => $ktiType->id,
            'file_path' => $path,
            'file_name' => $originalName,
            'file_type' => strtolower($extension),
            'status' => 'pending',
        ]);

        // Auto-add to this folder
        $this->folder->articles()->syncWithoutDetaching([$article->id]);

        // Dispatch AI analysis
        AnalyzeArticleJob::dispatch($article);

        // Sync folder context after article is added
        SyncFolderContextJob::dispatch($this->folder);

        $this->reset(['file', 'selectedKtiTypeId']);
        $this->showUploadModal = false;
        $this->folder->refresh();
        $this->dispatch('file-uploaded');
    }

    /**
     * Add an existing article to this folder.
     */
    public function addArticle(int $articleId): void
    {
        $article = Article::where('id', $articleId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $this->folder->articles()->syncWithoutDetaching([$article->id]);
        $this->folder->refresh();

        // Sync folder context in background
        SyncFolderContextJob::dispatch($this->folder);
    }

    /**
     * Remove an article from this folder (does not delete the article).
     */
    public function removeArticle(int $articleId): void
    {
        $this->folder->articles()->detach($articleId);
        $this->folder->refresh();

        // Sync folder context in background
        SyncFolderContextJob::dispatch($this->folder);

        $this->dispatch('notify', message: 'Artikel berhasil dikeluarkan dari folder ini.');
    }

    // ===== FOLDER CHAT =====

    /**
     * Send a chat message with folder context.
     */
    public function sendChatMessage(): void
    {
        $this->chatError = '';

        $this->validate([
            'chatMessage' => 'required|string|min:2|max:2000',
        ], [
            'chatMessage.required' => 'Tulis pertanyaanmu dulu.',
        ]);

        $this->isSendingChat = true;
        $message = trim($this->chatMessage);
        $this->chatMessage = '';

        try {
            $geminiService = app(GeminiService::class);
            $brainService = app(FolderBrainService::class);

            // Build folder context
            $folderContext = $brainService->buildContext($this->folder);

            // Get previous folder chat history
            $previousChats = ChatHistory::where('user_id', Auth::id())
                ->where('folder_id', $this->folder->id)
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

            // Send to Gemini with folder-specific model and cached context
            $contextText = $this->folder->context_cache ?: $brainService->buildSummaryText($this->folder);
            $response = $geminiService->folderChat(
                $message,
                $contextText,
                $this->folder->name,
                $previousChats
            );

            // Save to database with folder_id
            ChatHistory::create([
                'user_id' => Auth::id(),
                'article_id' => null,
                'folder_id' => $this->folder->id,
                'message' => $message,
                'response' => $response,
                'metadata' => [
                    'model' => config('services.gemini.model_folder_chat'),
                    'folder_name' => $this->folder->name,
                    'articles_count' => count($folderContext),
                ],
            ]);

            $this->dispatch('folder-chat-updated');
        } catch (\Exception $e) {
            $this->chatError = 'Gagal mengirim pesan: '.$e->getMessage();
            $this->chatMessage = $message;
        } finally {
            $this->isSendingChat = false;
        }
    }

    /**
     * Get folder chat history.
     */
    public function getFolderChatHistoryProperty()
    {
        return ChatHistory::where('user_id', Auth::id())
            ->where('folder_id', $this->folder->id)
            ->oldest()
            ->get();
    }

    public function render()
    {
        return view('livewire.folder-view', [
            'articles' => $this->articles,
            'ktiTypes' => $this->ktiTypes,
            'availableArticles' => $this->availableArticles,
            'folderChatHistory' => $this->folderChatHistory,
        ]);
    }
}
