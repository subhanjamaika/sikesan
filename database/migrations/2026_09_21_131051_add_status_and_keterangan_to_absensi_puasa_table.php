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
        Schema::table('absensi_puasa', function (Blueprint $table) {
            $table->enum('status_puasa', ['puasa', 'tidak_puasa', 'haid', 'sakit', 'izin'])->default('puasa')->after('tanggal');
            $table->string('keterangan')->nullable()->after('status_puasa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_puasa', function (Blueprint $table) {
            $table->dropColumn(['status_puasa', 'keterangan']);
        });
    }
};
