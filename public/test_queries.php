<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

$user = \App\Models\User::where('nama', 'like', '%Admin%')->first();
$kegiatan = \App\Models\JenisKegiatan::where('nama_kegiatan', 'Sholat Tarawih')->first();
$kamarId = \App\Models\UnitOrganisasi::where('nama', 'D.1 (UC. Charleston)')->value('id');
$daerahId = \App\Models\UnitOrganisasi::where('nama', 'Excellent Language')->value('id');

// CONTROLLER QUERY
$santriQueryC = \App\Models\Santri::query();
$allowedUnitIds = $user->getAllowedUnitIds();

if ($kegiatan && !is_null($kegiatan->unit_organisasi_id) && $user->hasAnyRole(['admin', 'superadmin'])) {
    $kegiatanUnitId = $kegiatan->unit_organisasi_id;
    $childIds = \App\Models\UnitOrganisasi::where('parent_id', $kegiatanUnitId)->pluck('id')->toArray();
    $santriQueryC->whereIn('unit_organisasi_id', array_merge([$kegiatanUnitId], $childIds));
} elseif (!empty($allowedUnitIds)) {
    $santriQueryC->whereIn('unit_organisasi_id', $allowedUnitIds);
}

if ($kegiatan && !is_null($kegiatan->program_id)) {
    $santriQueryC->where('program_id', $kegiatan->program_id);
}

if ($kamarId) {
    $santriQueryC->where('unit_organisasi_id', $kamarId);
}
echo "Controller count: " . $santriQueryC->count() . "\n";

// REQUEST QUERY
$santriQueryR = \App\Models\Santri::query();
$isLokal = $kegiatan && !is_null($kegiatan->unit_organisasi_id);
$targetUnitIds = $allowedUnitIds;

if ($isLokal && $user->hasAnyRole(['admin', 'superadmin'])) {
    $kegiatanUnitId = $kegiatan->unit_organisasi_id;
    $childIds = \App\Models\UnitOrganisasi::where('parent_id', $kegiatanUnitId)->pluck('id')->toArray();
    $targetUnitIds = array_merge([$kegiatanUnitId], $childIds);
}

if (!empty($targetUnitIds)) {
    $santriQueryR->whereIn('unit_organisasi_id', $targetUnitIds);
}

if ($kamarId) {
    $santriQueryR->where('unit_organisasi_id', $kamarId);
}
echo "Request count: " . $santriQueryR->count() . "\n";
