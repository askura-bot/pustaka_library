<?php

use App\Models\KtiType;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The required columns for the default "Article" template.
     */
    private const ARTICLE_COLUMNS = [
        'Judul',
        'Penulis',
        'Jurnal Publikasi',
        'Seri Jurnal',
        'Link DOI',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create default "Article" template for all existing users who don't have one
        User::query()
            ->whereDoesntHave('ktiTypes', function ($query) {
                $query->where('name', 'Article');
            })
            ->each(function (User $user) {
                $user->ktiTypes()->create([
                    'name' => 'Article',
                    'columns' => self::ARTICLE_COLUMNS,
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        KtiType::where('name', 'Article')->delete();
    }
};
