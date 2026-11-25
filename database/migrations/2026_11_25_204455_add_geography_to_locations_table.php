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
        Schema::table('locations', function (Blueprint $table) {
            $table->string('country_code', 2)->nullable();
            $table->string('region_code', 10)->default('');
            $table->unique(['country_code', 'region_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropUnique(['country_code', 'region_code']);
            $table->dropColumn(['country_code', 'region_code']);
        });
    }
};
