<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Enums\ProgramEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ambil data dari tabel programs sebelum tabel dihapus
        $programs = DB::table('programs')->get();
        
        $programMap = [];
        foreach ($programs as $prog) {
            $enumVal = null;
            $namaProgram = $prog->nama_program;
            
            // Konversi nama program ke Enum string
            switch ($namaProgram) {
                case "I'dadiyah": $enumVal = 'idadiyah'; break;
                case "Wustho Tahsin": $enumVal = 'wustho_tahsin'; break;
                case "Awwaliyah I": $enumVal = 'awwaliyah_i'; break;
                case "Wustho Kitab": $enumVal = 'wustho_kitab'; break;
                case "Awwaliyah II": $enumVal = 'awwaliyah_ii'; break;
                case "Wustho Bahasa": $enumVal = 'wustho_bahasa'; break;
                case "Wustho Tahfidz": $enumVal = 'wustho_tahfidz'; break;
                case "Al-Mahfudzi": $enumVal = 'al_mahfudzi'; break;
                case "Sifir": $enumVal = 'sifir'; break;
            }
            
            if ($enumVal) {
                $programMap[$prog->id] = $enumVal;
            }
        }

        // 2. Tidak ada foreign key pada santri, jadi lewati.
        // Schema::table('santri', function (Blueprint $table) {
        //     $table->dropForeign(['program_id']);
        // });

        // 3. Konversi data program_id di santri (dari UUID ke string Enum)
        foreach ($programMap as $uuid => $enumVal) {
            DB::table('santri')
                ->where('program_id', $uuid)
                ->update(['program_id' => $enumVal]);
        }

        // 4. Perbarui data di jenis_kegiatan agar sesuai dengan Enum (opsional karena di KegiatanDaerahController sudah pakai slug)
        // Kita standardisasi sesuai enum
        $validEnums = array_column(ProgramEnum::cases(), 'value');
        
        $jenisKegiatan = DB::table('jenis_kegiatan')->whereNotNull('program_id')->get();
        foreach ($jenisKegiatan as $jk) {
            $current = $jk->program_id;
            // Jika ada yang belum sesuai tapi mendekati
            if (!in_array($current, $validEnums)) {
                // contoh: jika wustho tahsin, jadikan wustho_tahsin
                $slug = preg_replace("/[^a-z0-9_']+/", '_', strtolower(trim($current)));
                $slug = trim($slug, '_');
                
                if (in_array($slug, $validEnums)) {
                    DB::table('jenis_kegiatan')->where('id', $jk->id)->update(['program_id' => $slug]);
                } else {
                    DB::table('jenis_kegiatan')->where('id', $jk->id)->update(['program_id' => null]);
                }
            }
        }

        // 5. Drop table programs
        Schema::dropIfExists('programs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Untuk rollback, kita buat kembali tabel programs
        Schema::create('programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('unit_organisasi_id')->constrained('unit_organisasi')->cascadeOnDelete();
            $table->string('nama_program');
            $table->timestamps();
        });
        
        Schema::table('santri', function (Blueprint $table) {
            // Karena relasi foreign_key harus sesuai dengan tipe data, namun data lama sudah tidak berupa UUID
            // Biasanya pada produksi kita memetakan ulang. Pada environment lokal kita kosongkan dulu foreign key-nya
            $table->foreign('program_id')->references('id')->on('programs')->nullOnDelete();
        });
    }
};
