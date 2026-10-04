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
        Schema::create('unit_organisasi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama');
            $table->enum('tipe', ['wilayah', 'daerah', 'sektor']);
            $table->uuid('parent_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')
                  ->references('id')
                  ->on('unit_organisasi')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_organisasi');
    }
};
