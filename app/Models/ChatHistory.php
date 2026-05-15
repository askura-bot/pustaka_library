<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'article_id', 'folder_id', 'message', 'response', 'metadata'])]
class ChatHistory extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * Get the user that owns this chat message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the article associated with this chat (null for global chat).
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Get the folder associated with this chat (null for non-folder chat).
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Scope: Get chat history for a specific article.
     */
    public function scopeForArticle($query, int $articleId)
    {
        return $query->where('article_id', $articleId);
    }

    /**
     * Scope: Get chat history for a specific folder.
     */
    public function scopeForFolder($query, int $folderId)
    {
        return $query->where('folder_id', $folderId);
    }

    /**
     * Scope: Get global chat history (no article context).
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('article_id')->whereNull('folder_id');
    }
}
