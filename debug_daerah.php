<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = \App\Models\User::whereHas('role', function($q){
    $q->where('nama_role', 'pengurus_daerah');
})->with('unitOrganisasi')->get();

foreach($users as $u) {
    echo "User: " . $u->nama . " (Unit: " . ($u->unitOrganisasi->nama ?? 'none') . " - ID: " . $u->unit_organisasi_id . ")\n";
    $unitScope = $u->getUnitScope();
    echo "Unit Scope: " . implode(", ", $unitScope) . "\n";
    
    $santriCount = \App\Models\Santri::whereIn('unit_organisasi_id', $unitScope)->count();
    echo "Santri count by unitScope: $santriCount\n";
    
    echo "------------\n";
}
