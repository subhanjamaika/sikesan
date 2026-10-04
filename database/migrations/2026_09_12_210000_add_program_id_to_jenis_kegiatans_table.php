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
            $table->string('program_id')->nullable()->after('unit_organisasi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jenis_kegiatan', function (Blueprint $table) {
            $table->dropColumn('program_id');
        });
    }
};
