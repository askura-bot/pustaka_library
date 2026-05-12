<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->jsonb('keywords')->nullable()->after('bibliography_output');
        });

        // Drop embedding column if it exists (pgvector no longer used)
        if (Schema::hasColumn('articles', 'embedding')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->dropColumn('embedding');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('keywords');
        });
    }
};
