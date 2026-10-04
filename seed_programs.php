<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UnitOrganisasi;
use App\Models\Program;
use Illuminate\Support\Str;

$mappings = [
    "Daerah I'dadiyah" => ["Sifir"],
    "Daerah Tahsin" => ["I'dadiyah", "Wustho Tahsin"],
    "Daerah Amtsilati" => ["Awwaliyah I", "Wustho Kitab"],
    "Daerah Excellent Language" => ["Awwaliyah II", "Wustho Bahasa"],
    "Daerah Tahfidzul Qur'an" => ["Wustho Tahfidz"],
];

// Optional: Hapus program yang sudah ada agar tidak menumpuk/ganda jika script ini dijalankan ulang
Program::truncate();

foreach ($mappings as $daerahName => $programs) {
    $daerah = UnitOrganisasi::where('nama', $daerahName)->first();
    
    if ($daerah) {
        foreach ($programs as $programName) {
            Program::create([
                'id' => Str::uuid()->toString(),
                'unit_organisasi_id' => $daerah->id,
                'nama_program' => $programName
            ]);
        }
        echo "Berhasil memetakan program ke " . $daerahName . "\n";
    } else {
        echo "PERINGATAN: " . $daerahName . " tidak ditemukan di tabel UnitOrganisasi!\n";
    }
}

echo "Semua program berhasil dipetakan ke daerah masing-masing!\n";
