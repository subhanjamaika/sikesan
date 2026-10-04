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
        Schema::create('aspek_penilaian', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->tinyInteger('nomor_urut');
            $table->string('nama_aspek');
            $table->text('indikator');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aspek_penilaian');
    }
};
