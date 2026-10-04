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
        Schema::create('pelanggaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('santri_id')->constrained('santri');
            $table->enum('kategori', ['umum', 'devisi']);
            $table->text('deskripsi');
            $table->date('tanggal');
            $table->foreignUuid('pencatat_id')->constrained('users');
            $table->enum('tingkat', ['sektor', 'daerah']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggaran');
    }
};
