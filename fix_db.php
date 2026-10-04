<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UnitOrganisasi;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Str;

$mappings = [
    "I'dadiyah" => ["Sifir"],
    "Tahsinul Qiro'ah" => ["I'dadiyah", "Wustho Tahsin"],
    "Amtsilati" => ["Awwaliyah I", "Wustho Kitab"],
    "Excellent Language" => ["Awwaliyah II", "Wustho Bahasa"],
    "Tahfidzul Qur'an" => ["Wustho Tahfidz"],
    "Al-Mahfudzi" => ["Al-Mahfudzi"], // Assuming Al-Mahfudzi has a program of the same name, or just to link it
];

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

// Fix user mappings
$userMappings = [
    "Pengurus Daerah Excellent Language" => "Excellent Language",
    "Pengurus Daerah I'dadiyah" => "I'dadiyah",
    "Pengurus Daerah Tahfidzul Qur'an" => "Tahfidzul Qur'an",
    "Pengurus Daerah Tahsin" => "Tahsinul Qiro'ah",
    "Pengurus Daerah Al-Mahfudzi" => "Al-Mahfudzi",
    "Pengurus Daerah Sifir" => "I'dadiyah", // Sifir is under I'dadiyah
    "Pengurus Daerah Amtsilati" => "Amtsilati",
];

foreach ($userMappings as $userName => $daerahName) {
    $user = User::where('nama', $userName)->first();
    $daerah = UnitOrganisasi::where('nama', $daerahName)->first();
    
    if ($user && $daerah) {
        $user->unit_organisasi_id = $daerah->id;
        $user->save();
        echo "Updated User: $userName with Daerah: $daerahName\n";
    }
}

echo "Database fixed!\n";
