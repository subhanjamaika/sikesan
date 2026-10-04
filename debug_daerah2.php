<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('nama', 'Pengurus Daerah Tahsin')->first();
echo "User ID: " . $user->id . "\n";
echo "Unit ID on User: " . $user->unit_organisasi_id . "\n";
$unit = \App\Models\UnitOrganisasi::find($user->unit_organisasi_id);
if ($unit) {
    echo "Unit Found! Nama: " . $unit->nama . " | Tipe: " . $unit->tipe . "\n";
} else {
    echo "Unit NOT FOUND in UnitOrganisasi table!\n";
}

$program = \App\Models\Program::find($user->unit_organisasi_id);
if ($program) {
    echo "Program Found! Nama: " . $program->nama_program . "\n";
} else {
    echo "NOT a program either!\n";
    $programByUnit = \App\Models\Program::where('unit_organisasi_id', $user->unit_organisasi_id)->get();
    echo "Programs linked to this unit_organisasi_id:\n";
    foreach($programByUnit as $p) {
        echo "- " . $p->nama_program . "\n";
    }
}

// Let's check where the user gets their scope from
$scope = $user->getUnitScope();
echo "Scope length: " . count($scope) . "\n";
