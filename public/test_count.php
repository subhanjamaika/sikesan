<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

$kamarId = \App\Models\UnitOrganisasi::where('nama', 'D.1 (UC. Charleston)')->value('id');
echo "Kamar ID: " . $kamarId . "\n";
$totalSantriKamar = \App\Models\Santri::where('unit_organisasi_id', $kamarId)->count();
echo "Total Santri Kamar: " . $totalSantriKamar . "\n";

$kegiatan = \App\Models\JenisKegiatan::where('nama_kegiatan', 'Sholat Tarawih')->first();
echo "Kegiatan Tarawih Program ID: " . ($kegiatan->program_id ?? 'null') . "\n";

$santriQuery = \App\Models\Santri::where('unit_organisasi_id', $kamarId);
if (!empty($kegiatan->program_id)) {
    $santriQuery->where('program_id', $kegiatan->program_id);
}
echo "Total Santri (Frontend Filter): " . $santriQuery->count() . "\n";
