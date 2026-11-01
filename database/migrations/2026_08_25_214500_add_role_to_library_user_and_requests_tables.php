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
        Schema::table('library_user', function (Blueprint $table) {
            $table->string('role')->default('user'); // manager, attendant, user
        });

        Schema::table('library_user_requests', function (Blueprint $table) {
            $table->string('role')->default('user'); // manager, attendant, user
        });

        // Copy user's global role to their pivot library relations
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            if ($user->role === 'admin' || $user->role === 'curator') {
                continue;
            }
            DB::table('library_user')
                ->where('user_id', $user->id)
                ->update(['role' => $user->role]);
        }

        // Copy user's global role to their pending requests
        $requests = DB::table('library_user_requests')->get();
        foreach ($requests as $req) {
            $user = DB::table('users')->find($req->user_id);
            if ($user) {
                DB::table('library_user_requests')
                    ->where('id', $req->id)
                    ->update(['role' => $user->role]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('library_user', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('library_user_requests', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
