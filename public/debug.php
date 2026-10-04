<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Total Santri: " . \App\Models\Santri::count() . "\n";
echo "Total Unit Organisasi: " . \App\Models\UnitOrganisasi::count() . "\n";
echo "Total Program: " . \App\Models\Program::count() . "\n";

$kegiatan = \App\Models\JenisKegiatan::where('nama_kegiatan', 'Sholat Tarawih')->first();
echo "Kegiatan Tarawih: " . ($kegiatan ? $kegiatan->id . " (Program ID: " . $kegiatan->program_id . ", Unit ID: " . $kegiatan->unit_organisasi_id . ")" : "Not found") . "\n";

$daerah = \App\Models\UnitOrganisasi::where('nama', 'Amtsilati')->first();
if ($daerah) {
    echo "Daerah Amtsilati ID: " . $daerah->id . "\n";
    $childIds = \App\Models\UnitOrganisasi::where('parent_id', $daerah->id)->pluck('id')->toArray();
    echo "Child Kamar IDs: " . implode(', ', $childIds) . "\n";
    
    $santriCount = \App\Models\Santri::whereIn('unit_organisasi_id', array_merge([$daerah->id], $childIds))->count();
    echo "Santri di Amtsilati: " . $santriCount . "\n";
} else {
    echo "Daerah Amtsilati Not found\n";
}

$sampleSantri = \App\Models\Santri::first();
if ($sampleSantri) {
    echo "Sample Santri: " . $sampleSantri->nama . ", Unit: " . $sampleSantri->unit_organisasi_id . ", Program: " . $sampleSantri->program_id . "\n";
}
