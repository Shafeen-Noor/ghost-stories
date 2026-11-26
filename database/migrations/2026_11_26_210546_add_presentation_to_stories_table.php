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
        Schema::table('stories', function (Blueprint $table) {
            $table->string('cover_path')->nullable();
            $table->string('background_path')->nullable();
            $table->json('gallery_paths')->nullable();
            $table->string('cover_preset')->default('auto');
            $table->string('backdrop')->default('none');
            $table->string('reading_theme')->default('light');
            $table->string('cover_layout')->default('split');
            $table->string('cover_position')->default('center');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn(['cover_path', 'background_path', 'gallery_paths', 'cover_preset', 'backdrop', 'reading_theme', 'cover_layout', 'cover_position']);
        });
    }
};
