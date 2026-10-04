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
        Schema::table('jenis_kegiatan', function (Blueprint $table) {
            $table->foreignUuid('unit_organisasi_id')->nullable()->after('id')->constrained('unit_organisasi')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jenis_kegiatan', function (Blueprint $table) {
            $table->dropForeign(['unit_organisasi_id']);
            $table->dropColumn('unit_organisasi_id');
        });
    }
};
