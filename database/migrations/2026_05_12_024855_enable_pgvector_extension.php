<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Attempts to enable pgvector. If the extension is not installed on the
     * PostgreSQL server, it logs a warning and continues gracefully.
     */
    public function up(): void
    {
        try {
            DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
        } catch (Exception $e) {
            Log::warning('pgvector extension not available: '.$e->getMessage());
            Log::warning('Semantic search features will be disabled until pgvector is installed.');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement('DROP EXTENSION IF EXISTS vector');
        } catch (Exception $e) {
            // Extension may not exist, that's fine
        }
    }
};
