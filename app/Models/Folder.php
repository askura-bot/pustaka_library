<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'name', 'description', 'context_cache'])]
class Folder extends Model
{
    /**
     * Get the user that owns the folder.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the articles in this folder (many-to-many).
     */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_folder')->withTimestamps();
    }

    /**
     * Get the chat histories for this folder.
     */
    public function chatHistories(): HasMany
    {
        return $this->hasMany(ChatHistory::class);
    }
}
