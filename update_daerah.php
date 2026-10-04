<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UnitOrganisasi;

$names = [
    "I'dadiyah",
    "Tahsin",
    "Amtsilati",
    "Excellent Language",
    "Tahfidzul Qur'an",
    "Al-Mahfudzi",
];

$daerahs = UnitOrganisasi::where('tipe', 'daerah')->orderBy('nama')->get();

foreach($daerahs as $index => $daerah) {
    if (isset($names[$index])) {
        // Hapus kata "Daerah" dari nama jika diinginkan, atau tambahkan
        $daerah->nama = 'Daerah ' . $names[$index];
        $daerah->save();
    } else {
        // Sisa daerah yang ke-7 (jika ada)
        $daerah->nama = 'Daerah Khusus';
        $daerah->save();
    }
}

echo "Berhasil memperbarui nama-nama Daerah sesuai permintaan!";
