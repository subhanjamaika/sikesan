<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Pemetaan akun:
     * - Role Pengurus Asrama/Daerah: asrama_id diisi.
     * - Role Wali Asuh: kamar_id diisi (1 Wali Asuh = 1 Kamar).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('asrama_id')->nullable()->after('unit_organisasi_id');
            $table->uuid('kamar_id')->nullable()->after('asrama_id');

            $table->foreign('asrama_id')
                  ->references('id')
                  ->on('asramas')
                  ->nullOnDelete();

            $table->foreign('kamar_id')
                  ->references('id')
                  ->on('kamars')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['asrama_id']);
            $table->dropForeign(['kamar_id']);
            $table->dropColumn(['asrama_id', 'kamar_id']);
        });
    }
};
