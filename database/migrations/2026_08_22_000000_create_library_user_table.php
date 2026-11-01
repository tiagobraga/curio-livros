<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('library_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('library_id')->constrained('libraries')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'library_id']);
        });

        // Copy existing relationships from users to library_user
        $users = DB::table('users')->whereNotNull('library_id')->get();
        foreach ($users as $user) {
            DB::table('library_user')->insert([
                'user_id' => $user->id,
                'library_id' => $user->library_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Drop the library_id column from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['library_id']);
            $table->dropColumn('library_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('library_id')->nullable()->constrained('libraries')->onDelete('set null');
        });

        Schema::dropIfExists('library_user');
    }
};
