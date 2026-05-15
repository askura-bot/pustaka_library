<?php

namespace App\Livewire;

use App\Jobs\AnalyzeArticleJob;
use App\Models\Article;
use App\Models\Folder;
use App\Models\KtiType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Library Dashboard')]
class LibraryDashboard extends Component
{
    use WithFileUploads;

    public $file;

    public string $selectedKtiTypeId = '';

    public string $search = '';

    public bool $showUploadModal = false;

    public bool $showDeleteModal = false;

    public ?int $articleToDelete = null;

    /** @var array<int, string> */
    public array $articleFolderNames = [];

    // Folder properties
    public bool $showFolderModal = false;

    public ?int $editingFolderId = null;

    public string $folderName = '';

    public string $folderDescription = '';

    public function getArticlesProperty()
    {
        $query = Auth::user()->articles()->with(['ktiType', 'folders'])->latest();

        if (trim($this->search) !== '') {
            $query = $this->applySmartSearch($query, trim($this->search));
        }

        return $query->get();
    }

    public function getKtiTypesProperty()
    {
        return Auth::user()->ktiTypes()->get();
    }

    /**
     * Apply smart keyword search across multiple columns with ranking.
     * Articles matching keywords column are prioritized.
     */
    protected function applySmartSearch($query, string $term)
    {
        $searchTerm = mb_strtolower($term);

        return $query
            ->where(function ($q) use ($searchTerm) {
                // Search in title
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$searchTerm}%"])
                    // Search in author
                    ->orWhereRaw('LOWER(author) LIKE ?', ["%{$searchTerm}%"])
                    // Search in file_name
                    ->orWhereRaw('LOWER(file_name) LIKE ?', ["%{$searchTerm}%"])
                    // Search in keywords (JSONB array text search)
                    ->orWhereRaw('keywords::text ILIKE ?', ["%{$searchTerm}%"])
                    // Search in analysis_results (full JSONB text search)
                    ->orWhereRaw('analysis_results::text ILIKE ?', ["%{$searchTerm}%"]);
            })
            // Ranking: keyword matches first, then title, then others
            ->orderByRaw('
                CASE
                    WHEN keywords::text ILIKE ? THEN 0
                    WHEN LOWER(title) LIKE ? THEN 1
                    WHEN LOWER(author) LIKE ? THEN 2
                    ELSE 3
                END ASC
            ', ["%{$searchTerm}%", "%{$searchTerm}%", "%{$searchTerm}%"])
            ->orderBy('created_at', 'desc');
    }

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

        AnalyzeArticleJob::dispatch($article);

        $this->reset(['file', 'selectedKtiTypeId']);
        $this->showUploadModal = false;
        $this->dispatch('file-uploaded');
    }

    public function confirmDelete(int $id): void
    {
        $article = Article::where('id', $id)->where('user_id', Auth::id())->with('folders')->firstOrFail();
        $this->articleToDelete = $article->id;
        $this->articleFolderNames = $article->folders->pluck('name')->toArray();
        $this->showDeleteModal = true;
    }

    public function deleteArticle(): void
    {
        if ($this->articleToDelete) {
            $article = Article::where('id', $this->articleToDelete)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            if (Storage::disk('local')->exists($article->file_path)) {
                Storage::disk('local')->delete($article->file_path);
            }

            $article->delete();

            $this->showDeleteModal = false;
            $this->articleToDelete = null;
            $this->articleFolderNames = [];
        }
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->articleToDelete = null;
        $this->articleFolderNames = [];
    }

    // ===== FOLDER MANAGEMENT =====

    public function getFoldersProperty()
    {
        return Auth::user()->folders()->withCount('articles')->latest()->get();
    }

    public function openFolderModal(?int $folderId = null): void
    {
        if ($folderId) {
            $folder = Folder::where('id', $folderId)->where('user_id', Auth::id())->firstOrFail();
            $this->editingFolderId = $folder->id;
            $this->folderName = $folder->name;
            $this->folderDescription = $folder->description ?? '';
        } else {
            $this->editingFolderId = null;
            $this->folderName = '';
            $this->folderDescription = '';
        }
        $this->showFolderModal = true;
    }

    public function saveFolder(): void
    {
        $this->validate([
            'folderName' => 'required|string|max:255',
            'folderDescription' => 'nullable|string|max:500',
        ], [
            'folderName.required' => 'Nama folder tidak boleh kosong.',
        ]);

        if ($this->editingFolderId) {
            $folder = Folder::where('id', $this->editingFolderId)->where('user_id', Auth::id())->firstOrFail();
            $folder->update([
                'name' => $this->folderName,
                'description' => $this->folderDescription ?: null,
            ]);
        } else {
            Auth::user()->folders()->create([
                'name' => $this->folderName,
                'description' => $this->folderDescription ?: null,
            ]);
        }

        $this->showFolderModal = false;
        $this->reset(['folderName', 'folderDescription', 'editingFolderId']);
    }

    public function deleteFolder(int $id): void
    {
        $folder = Folder::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        // Only delete pivot records and chat histories — articles stay intact
        // Chat histories cascade automatically via FK (cascadeOnDelete)
        // Pivot records cascade automatically via FK (cascadeOnDelete on article_folder)
        $folder->delete();
    }

    public function render()
    {
        return view('livewire.library-dashboard', [
            'articles' => $this->articles,
            'ktiTypes' => $this->ktiTypes,
            'folders' => $this->folders,
        ]);
    }
}
