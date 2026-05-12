<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a vector(768) column for semantic search embeddings.
     * Requires pgvector extension to be installed.
     */
    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE articles ADD COLUMN embedding vector(768)');
        } catch (Exception $e) {
            Log::warning('Could not add embedding column (pgvector may not be installed): '.$e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('articles', 'embedding')) {
            Schema::table('articles', function ($table) {
                $table->dropColumn('embedding');
            });
        }
    }
};
