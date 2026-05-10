<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
#[Title('Detail Artikel')]
class ArticleDetail extends Component
{
    public Article $article;

    public function mount(Article $article)
    {
        // Pastikan hanya pemilik yang bisa melihat
        if ($article->user_id !== Auth::id()) {
            abort(403);
        }
        
        $this->article = $article;
    }

    public function reanalyze()
    {
        if ($this->article->status === 'failed') {
            $this->article->update(['status' => 'pending']);
            \App\Jobs\AnalyzeArticleJob::dispatch($this->article);
            $this->article->refresh();
        }
    }

    public function render()
    {
        return view('livewire.article-detail', [
            'article' => $this->article,
        ]);
    }
}
