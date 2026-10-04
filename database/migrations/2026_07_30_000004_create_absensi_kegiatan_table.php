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
        Schema::create('absensi_kegiatan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('santri_id')->constrained('santri')->onDelete('cascade');
            $table->foreignUuid('jenis_kegiatan_id')->constrained('jenis_kegiatan')->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa']);
            $table->foreignUuid('pencatat_id')->constrained('users');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['santri_id', 'jenis_kegiatan_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_kegiatan');
    }
};
