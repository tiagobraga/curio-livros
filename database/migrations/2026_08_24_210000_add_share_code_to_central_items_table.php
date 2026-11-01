<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('central_items', function (Blueprint $table) {
            $table->string('share_code', 6)->unique()->nullable()->index();
        });

        // Generate unique share_code for all existing books
        $items = DB::table('central_items')->whereNull('share_code')->get();
        foreach ($items as $item) {
            do {
                $code = Str::random(6);
                $exists = DB::table('central_items')->where('share_code', $code)->exists();
            } while ($exists);

            DB::table('central_items')->where('id', $item->id)->update(['share_code' => $code]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('central_items', function (Blueprint $table) {
            $table->dropColumn('share_code');
        });
    }
};
