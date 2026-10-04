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
        Schema::create('pembinaan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pelanggaran_id')->constrained('pelanggaran');
            $table->string('jenis_pembinaan');
            $table->date('tanggal');
            $table->text('hasil')->nullable();
            $table->foreignUuid('pembina_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembinaan');
    }
};
