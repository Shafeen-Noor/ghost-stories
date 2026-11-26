<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->timestamps();
            $table->unique(['user_id', 'name']);
        });
        Schema::create('reading_collection_story', function (Blueprint $table) {
            $table->foreignId('reading_collection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('story_id')->constrained()->cascadeOnDelete();
            $table->primary(['reading_collection_id', 'story_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_collection_story');
        Schema::dropIfExists('reading_collections');
    }
};
