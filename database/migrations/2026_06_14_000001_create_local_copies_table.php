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
        Schema::create('local_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('central_item_id')->constrained('central_items')->onDelete('cascade');
            $table->foreignId('library_id')->constrained('libraries')->onDelete('cascade');
            $table->string('call_number')->nullable(); // Número de chamada local
            $table->string('shelf_location')->nullable(); // Localização física (ex: Estante A, Prateleira 2)
            $table->string('status')->default('available'); // available, borrowed, damaged, lost
            $table->integer('quantity')->default(1); // Quantidade física total nesta unidade
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('local_copies');
    }
};
