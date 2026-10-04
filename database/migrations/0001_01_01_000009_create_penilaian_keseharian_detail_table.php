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
        Schema::create('penilaian_keseharian_detail', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('penilaian_id')->constrained('penilaian_keseharian')->onDelete('cascade');
            $table->foreignUuid('aspek_id')->constrained('aspek_penilaian');
            $table->tinyInteger('skor'); // Skala 1-4
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian_keseharian_detail');
    }
};
