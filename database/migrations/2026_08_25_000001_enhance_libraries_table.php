<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('libraries', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_public')->default(true);
            $table->string('share_code', 10)->nullable()->unique();
            $table->string('phone')->nullable();
        });

        // Generate unique share codes for existing libraries
        $libraries = DB::table('libraries')->get();
        foreach ($libraries as $lib) {
            do {
                $code = strtoupper(Str::random(6));
            } while (DB::table('libraries')->where('share_code', $code)->exists());

            DB::table('libraries')->where('id', $lib->id)->update([
                'share_code' => $code,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('libraries', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropColumn(['owner_id', 'description', 'address', 'is_public', 'share_code', 'phone']);
        });
    }
};
