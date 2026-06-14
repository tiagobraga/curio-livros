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
        Schema::create('central_items', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->string('isbn')->nullable()->index();
            $table->string('item_type')->default('book'); // book, magazine, comic, record, map, other
            $table->jsonb('metadata')->nullable(); // BIBFRAME JSON-LD Work/Instance properties
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('central_items');
    }
};
