<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'kti_type_id', 'file_path', 'file_name', 'file_type', 'title', 'author', 'year', 'status', 'analysis_results', 'citation_output', 'bibliography_output', 'keywords'])]
class Article extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'analysis_results' => 'array',
            'keywords' => 'array',
        ];
    }

    /**
     * Get the user that owns the article.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the KTI type that owns the article.
     */
    public function ktiType(): BelongsTo
    {
        return $this->belongsTo(KtiType::class);
    }

    /**
     * Get the chat histories for this article.
     */
    public function chatHistories(): HasMany
    {
        return $this->hasMany(ChatHistory::class);
    }
}
