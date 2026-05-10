<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Article;
use App\Models\KtiType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.app')]
#[Title('Library Dashboard')]
class LibraryDashboard extends Component
{
    use WithFileUploads;

    public $file;
    public $selectedKtiTypeId = '';
    
    public $showUploadModal = false;
    public $showDeleteModal = false;
    public $articleToDelete = null;

    public function getArticlesProperty()
    {
        return Auth::user()->articles()->with('ktiType')->latest()->get();
    }

    public function getKtiTypesProperty()
    {
        return Auth::user()->ktiTypes()->get();
    }

    public function uploadFile()
    {
        $this->validate([
            'selectedKtiTypeId' => 'required|exists:kti_types,id',
            'file' => 'required|mimes:pdf,docx|max:10240', // 10MB
        ], [
            'selectedKtiTypeId.required' => 'Pilih Jenis KTI terlebih dahulu.',
            'file.required' => 'Pilih file yang ingin diunggah.',
            'file.mimes' => 'Hanya file PDF atau DOCX yang didukung.',
            'file.max' => 'Ukuran maksimal file adalah 10MB.',
        ]);

        $user = Auth::user();

        // Validasi kepemilikan kti_type
        $ktiType = KtiType::where('id', $this->selectedKtiTypeId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $originalName = $this->file->getClientOriginalName();
        $extension = $this->file->getClientOriginalExtension();
        
        // Simpan file ke storage private
        $path = $this->file->storeAs(
            'articles/' . $user->id,
            uniqid() . '_' . time() . '.' . $extension,
            'local'
        );

        // Buat record di database
        $user->articles()->create([
            'kti_type_id' => $ktiType->id,
            'file_path' => $path,
            'file_name' => $originalName,
            'file_type' => strtolower($extension),
            'status' => 'pending',
        ]);

        // Reset state
        $this->reset(['file', 'selectedKtiTypeId']);
        $this->showUploadModal = false;
        
        // Reset file input (mengakali input[type="file"] Livewire yang menempel)
        $this->dispatch('file-uploaded');
    }

    public function confirmDelete($id)
    {
        $article = Article::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $this->articleToDelete = $article->id;
        $this->showDeleteModal = true;
    }

    public function deleteArticle()
    {
        if ($this->articleToDelete) {
            $article = Article::where('id', $this->articleToDelete)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            // Hapus file fisik
            if (Storage::disk('local')->exists($article->file_path)) {
                Storage::disk('local')->delete($article->file_path);
            }

            // Hapus record database
            $article->delete();
            
            $this->showDeleteModal = false;
            $this->articleToDelete = null;
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->articleToDelete = null;
    }

    public function render()
    {
        return view('livewire.library-dashboard', [
            'articles' => $this->articles,
            'ktiTypes' => $this->ktiTypes,
        ]);
    }
}
