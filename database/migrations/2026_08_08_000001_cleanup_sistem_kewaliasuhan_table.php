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
        // 1. Membersihkan kolom-kolom lama dari tabel users jika masih ada
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'asrama_id')) {
                    // Safe drop foreign key and column
                    $table->dropForeign(['asrama_id']);
                    $table->dropColumn('asrama_id');
                }
                
                if (Schema::hasColumn('users', 'kamar_id')) {
                    $table->dropForeign(['kamar_id']);
                    $table->dropColumn('kamar_id');
                }
                
                if (Schema::hasColumn('users', 'daerah_binaan_id')) {
                    $table->dropForeign(['daerah_binaan_id']);
                    $table->dropColumn('daerah_binaan_id');
                }
            });
        }

        // 2. Membersihkan tabel-tabel usang
        Schema::dropIfExists('kamars');
        Schema::dropIfExists('asramas');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Karena ini adalah proses cleanup kode usang (dead code), 
        // down() dibiarkan kosong agar tidak secara tidak sengaja mengembalikan struktur usang.
    }
};
