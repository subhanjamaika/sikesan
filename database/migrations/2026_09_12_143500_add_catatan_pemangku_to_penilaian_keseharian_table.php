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
        Schema::table('penilaian_keseharian', function (Blueprint $table) {
            $table->text('catatan_pemangku')->nullable()->after('rencana_tindak_lanjut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penilaian_keseharian', function (Blueprint $table) {
            $table->dropColumn('catatan_pemangku');
        });
    }
};
