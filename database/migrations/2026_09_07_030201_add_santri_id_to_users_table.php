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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'santri_id')) {
                $table->uuid('santri_id')->nullable()->after('unit_organisasi_id');
                $table->foreign('santri_id')->references('id')->on('santri')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'santri_id')) {
                $table->dropForeign(['santri_id']);
                $table->dropColumn('santri_id');
            }
        });
    }
};
