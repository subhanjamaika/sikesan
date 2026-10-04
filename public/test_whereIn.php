<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

$daerahId = \App\Models\UnitOrganisasi::where('nama', 'Excellent Language')->value('id');
$childIds = \App\Models\UnitOrganisasi::where('parent_id', $daerahId)->pluck('id')->toArray();

echo "Daerah ID: " . $daerahId . "\n";
echo "Child IDs: " . implode(", ", $childIds) . "\n";

$kamarId = \App\Models\UnitOrganisasi::where('nama', 'D.1 (UC. Charleston)')->value('id');
echo "Kamar ID (D.1): " . $kamarId . "\n";
echo "Is D.1 in childIds? " . (in_array($kamarId, $childIds) ? 'Yes' : 'No') . "\n";

$santriCount = \App\Models\Santri::whereIn('unit_organisasi_id', array_merge([$daerahId], $childIds))->count();
echo "Santri Count whereIn: " . $santriCount . "\n";
