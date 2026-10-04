<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$uuids = DB::table('santri')->where('program_id', 'like', '%-%')->distinct()->pluck('program_id');

echo "Found UUIDs:\n";
foreach($uuids as $u) {
    echo $u . "\n";
    // Coba tebak program dari kamar
    $santri = \App\Models\Santri::with('unitOrganisasi.parent')->where('program_id', $u)->first();
    if ($santri && $santri->unitOrganisasi && $santri->unitOrganisasi->parent) {
        $daerahName = $santri->unitOrganisasi->parent->nama;
        echo "  - Sample Daerah: " . $daerahName . "\n";
    } else {
        echo "  - No Kamar/Daerah found for sample santri.\n";
    }
}
