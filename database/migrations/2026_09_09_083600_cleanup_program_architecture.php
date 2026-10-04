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
        // Drop program_id from absensi_kegiatan if exists
        if (Schema::hasColumn('absensi_kegiatan', 'program_id')) {
            Schema::table('absensi_kegiatan', function (Blueprint $table) {
                // Drop foreign key first if it exists. 
                // We use a try-catch or assume the standard naming if it was added.
                // Assuming standard naming: absensi_kegiatan_program_id_foreign
                // Let's just drop column, which might fail if FK is not dropped, 
                // but if there's no FK, it's fine. To be safe:
                $table->dropForeign(['program_id']);
                $table->dropColumn('program_id');
            });
        }

        // Drop program_id from santri if exists
        if (Schema::hasColumn('santri', 'program_id')) {
            Schema::table('santri', function (Blueprint $table) {
                $table->dropForeign(['program_id']);
                $table->dropColumn('program_id');
            });
        }

        // Drop programs table
        Schema::dropIfExists('programs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-creating programs table just in case rollback is needed
        Schema::create('programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_program');
            $table->timestamps();
        });

        if (Schema::hasTable('santri')) {
            Schema::table('santri', function (Blueprint $table) {
                $table->uuid('program_id')->nullable()->after('wali_santri_kontak');
                $table->foreign('program_id')->references('id')->on('programs')->nullOnDelete();
            });
        }
    }
};
