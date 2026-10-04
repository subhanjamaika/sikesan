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
        Schema::create('penilaian_keseharian', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('santri_id')->constrained('santri');
            $table->foreignUuid('wali_asuh_id')->constrained('users');
            $table->string('periode', 7); // Format: YYYY-MM, mis. "2026-07"
            $table->date('tanggal_penilaian');
            $table->tinyInteger('jumlah_skor'); // Hasil SUM dari detail
            $table->decimal('nilai_akhir', 5, 2); // Hasil (jumlah_skor/60)*100
            $table->enum('kategori', [
                'istimewa',
                'sangat_baik',
                'baik',
                'cukup',
                'perlu_pembinaan_intensif',
            ]);
            $table->text('catatan_kelebihan');
            $table->text('catatan_peningkatan');
            $table->text('rencana_tindak_lanjut');
            $table->timestamps();

            // Composite index untuk query penilaian per santri dan periode
            $table->index(['santri_id', 'periode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian_keseharian');
    }
};
