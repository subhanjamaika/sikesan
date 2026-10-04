<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan daerah_binaan_id (UUID nullable) pada tabel users
     * untuk penugasan Daerah Binaan bagi Kabag Daerah dan Wali Asuh.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('daerah_binaan_id')->nullable()->after('kamar_id');

            $table->foreign('daerah_binaan_id')
                  ->references('id')
                  ->on('unit_organisasi')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['daerah_binaan_id']);
            $table->dropColumn(['daerah_binaan_id']);
        });
    }
};
