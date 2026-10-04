<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// User = Pengurus Daerah Tahsin
$user = \App\Models\User::where('nama', 'Pengurus Daerah Tahsin')->first();
echo "User: " . $user->nama . " (Unit ID: " . $user->unit_organisasi_id . ")\n";

$kegiatan = \App\Models\JenisKegiatan::where('program_id', 'wustho_tahsin')->first();
echo "Kegiatan: " . ($kegiatan ? $kegiatan->nama_kegiatan : 'Not Found') . "\n";

$santriQuery = \App\Models\Santri::query();
$santriQuery->inUnitOrProgram([$user->unit_organisasi_id]);

if ($kegiatan && $kegiatan->program_id) {
    // Simulasi filter
    $santriQuery->where('program_id', $kegiatan->program_id);
}

echo "SQL: " . $santriQuery->toSql() . "\n";
echo "Bindings: " . json_encode($santriQuery->getBindings()) . "\n";

$santris = $santriQuery->get();
echo "Santri count: " . $santris->count() . "\n";
foreach($santris as $s) {
    echo "- " . $s->nama . " (Program: " . ($s->program_id ? $s->program_id->value : 'null') . ")\n";
}
