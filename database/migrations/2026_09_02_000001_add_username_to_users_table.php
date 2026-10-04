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
        // 1. Tambah kolom username jika belum ada (nullable dulu, tanpa unique)
        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username')->nullable()->after('nama');
            });
        }

        // 2. Isi username untuk user yang sudah ada berdasarkan nama/email/id
        DB::table('users')->whereNull('username')->orWhere('username', '')->orderBy('created_at')->each(function ($user) {
            $baseUsername = $user->email
                ? explode('@', $user->email)[0]
                : strtolower(str_replace(' ', '_', $user->nama ?? 'user_' . substr($user->id, 0, 8)));

            // Pastikan username unique
            $username = $baseUsername;
            $counter = 1;
            while (DB::table('users')->where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $baseUsername . '_' . $counter;
                $counter++;
            }

            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        });

        // 3. Sekarang set not-null dan unique constraint
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->unique()->change();
        });

        // 4. Jadikan email nullable karena SSO menggunakan username
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
            $table->string('email')->nullable(false)->change();
        });
    }
};
