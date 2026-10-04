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
        // absensi_kegiatan sudah berhasil di-migrate sebelumnya
        if (!Schema::hasColumn('absensi_kegiatan', 'is_archived')) {
            Schema::table('absensi_kegiatan', function (Blueprint $table) {
                $table->boolean('is_archived')->default(false)->after('keterangan');
                $table->index('is_archived');
            });
        }

        Schema::table('absensi_puasa', function (Blueprint $table) {
            $table->boolean('is_archived')->default(false)->after('pencatat_id');
            $table->index('is_archived');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_kegiatan', function (Blueprint $table) {
            $table->dropIndex(['is_archived']);
            $table->dropColumn('is_archived');
        });

        Schema::table('absensi_puasa', function (Blueprint $table) {
            $table->dropIndex(['is_archived']);
            $table->dropColumn('is_archived');
        });
    }
};
