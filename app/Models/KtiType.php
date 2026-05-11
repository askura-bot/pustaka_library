<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'name', 'columns'])]
class KtiType extends Model
{
    /**
     * Protected columns that cannot be removed from the "Article" template.
     *
     * @var array<int, string>
     */
    public const ARTICLE_PROTECTED_COLUMNS = [
        'Judul',
        'Penulis',
        'Jurnal Publikasi',
        'Seri Jurnal',
        'Link DOI',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'columns' => 'array',
        ];
    }

    /**
     * Check if this is the default "Article" template.
     */
    public function isArticleTemplate(): bool
    {
        return $this->name === 'Article';
    }

    /**
     * Check if a column is protected (cannot be deleted) for this template.
     */
    public function isProtectedColumn(string $columnName): bool
    {
        if (! $this->isArticleTemplate()) {
            return false;
        }

        return in_array($columnName, self::ARTICLE_PROTECTED_COLUMNS, true);
    }

    /**
     * Get the articles for the KTI type.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
