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
        Schema::create('perizinan_sekolah', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('santri_id')->constrained('santri');
            $table->enum('jenis', ['sakit', 'kepentingan_wilayah']);
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->foreignUuid('pencatat_id')->constrained('users');
            $table->enum('lingkup_pencatat', ['bkwa', 'daerah']);
            $table->enum('status', ['diajukan', 'disetujui', 'ditolak'])->default('diajukan');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Composite index untuk query perizinan per santri dan rentang tanggal
            $table->index(['santri_id', 'tgl_mulai', 'tgl_selesai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perizinan_sekolah');
    }
};
