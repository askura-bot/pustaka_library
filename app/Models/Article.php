<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'kti_type_id', 'file_path', 'file_name', 'file_type', 'status'])]
class Article extends Model
{
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
}
