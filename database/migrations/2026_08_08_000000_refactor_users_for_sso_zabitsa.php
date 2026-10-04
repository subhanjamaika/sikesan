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
        // 1. Buat tabel pivot penugasan_pengurus
        Schema::create('penugasan_pengurus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('unit_organisasi_id')->constrained('unit_organisasi')->onDelete('cascade');
            $table->timestamps();
        });

        // 2. Hapus kolom dari users
        Schema::table('users', function (Blueprint $table) {
            // Check if foreign keys exist before dropping, Laravel sometimes fails if not.
            // Typically in SQLite it's tricky, but in MySQL it's straightforward.
            $table->dropForeign(['asrama_id']);
            $table->dropForeign(['kamar_id']);
            // daerah_binaan_id foreign key constraint is missing in the view? Wait, let's just drop them safely
            // from 2026_08_06_000006_add_daerah_binaan_id_to_users_table.php we know it exists.
            $table->dropForeign(['daerah_binaan_id']);
            
            $table->dropColumn(['asrama_id', 'kamar_id', 'daerah_binaan_id']);
        });

        // 3. Hapus tabel lama (yang tidak dipakai lagi)
        Schema::dropIfExists('penugasan_wali_asuh');
        Schema::dropIfExists('kamars');
        Schema::dropIfExists('asramas');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('asramas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_asrama');
            $table->timestamps();
        });

        Schema::create('kamars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_kamar');
            $table->timestamps();
        });

        Schema::create('penugasan_wali_asuh', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('kamar_id')->constrained('unit_organisasi')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('asrama_id')->nullable();
            $table->uuid('kamar_id')->nullable();
            $table->uuid('daerah_binaan_id')->nullable();

            $table->foreign('asrama_id')->references('id')->on('asramas')->nullOnDelete();
            $table->foreign('kamar_id')->references('id')->on('kamars')->nullOnDelete();
            $table->foreign('daerah_binaan_id')->references('id')->on('unit_organisasi')->nullOnDelete();
        });

        Schema::dropIfExists('penugasan_pengurus');
    }
};
