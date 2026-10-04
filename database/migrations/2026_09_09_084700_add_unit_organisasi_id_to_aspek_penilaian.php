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
        Schema::table('aspek_penilaian', function (Blueprint $table) {
            $table->uuid('unit_organisasi_id')->nullable()->after('id');
            $table->foreign('unit_organisasi_id')->references('id')->on('unit_organisasi')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aspek_penilaian', function (Blueprint $table) {
            $table->dropForeign(['unit_organisasi_id']);
            $table->dropColumn('unit_organisasi_id');
        });
    }
};
