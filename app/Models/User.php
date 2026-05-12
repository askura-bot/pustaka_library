<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'google_id', 'avatar'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The required columns for the default "Article" KTI template.
     */
    public const DEFAULT_ARTICLE_COLUMNS = [
        'Judul',
        'Penulis',
        'Jurnal Publikasi',
        'Seri Jurnal',
        'Link DOI',
    ];

    /**
     * Boot the model and register events.
     */
    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->ktiTypes()->create([
                'name' => 'Article',
                'columns' => self::DEFAULT_ARTICLE_COLUMNS,
            ]);
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the KTI types for the user.
     */
    public function ktiTypes(): HasMany
    {
        return $this->hasMany(KtiType::class);
    }

    /**
     * Get the articles for the user.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Get the chat histories for the user.
     */
    public function chatHistories(): HasMany
    {
        return $this->hasMany(ChatHistory::class);
    }
}
