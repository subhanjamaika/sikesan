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
        Schema::table('unit_organisasi', function (Blueprint $table) {
            $table->boolean('is_puasa_wajib')->default(false)->after('tipe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_organisasi', function (Blueprint $table) {
            $table->dropColumn('is_puasa_wajib');
        });
    }
};

