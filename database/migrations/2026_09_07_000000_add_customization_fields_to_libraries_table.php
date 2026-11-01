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
        Schema::table('libraries', function (Blueprint $table) {
            $table->string('color_scheme')->default('emerald')->after('is_active');
            $table->string('custom_logo_url', 2048)->nullable()->after('color_scheme');
            $table->string('subtitle')->nullable()->after('custom_logo_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('libraries', function (Blueprint $table) {
            $table->dropColumn(['color_scheme', 'custom_logo_url', 'subtitle']);
        });
    }
};
