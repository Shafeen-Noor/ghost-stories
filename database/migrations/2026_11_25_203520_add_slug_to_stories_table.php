<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            Schema::table('stories', function (Blueprint $table): void {
                $table->string('slug')->nullable()->unique();
            });

            DB::table('stories')->orderBy('id')->each(function (object $story): void {
                $base = substr((string) str($story->title)->slug('-'), 0, 220) ?: 'story';
                $slug = $base;
                $suffix = 2;

                while (DB::table('stories')->where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$suffix++;
                }

                DB::table('stories')->where('id', $story->id)->update(['slug' => $slug]);
            });

            Schema::table('stories', function (Blueprint $table): void {
                $table->string('slug')->nullable(false)->change();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            Schema::table('stories', function (Blueprint $table): void {
                $table->dropColumn('slug');
            });
        });
    }
};
