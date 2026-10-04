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
        Schema::create('perlengkapan_santri', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('santri_id')->constrained('santri');
            $table->string('nama_barang');
            $table->enum('status', ['ada', 'hilang', 'rusak'])->default('ada');
            $table->date('tanggal_cek');
            $table->foreignUuid('pencatat_id')->constrained('users');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perlengkapan_santri');
    }
};
