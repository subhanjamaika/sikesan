<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$daerahs = \App\Models\UnitOrganisasi::where('tipe', 'daerah')->orWhere('tipe', 'daerah_pendidikan')->get();
echo "Daftar Daerah:\n";
foreach($daerahs as $d) {
    echo "- " . $d->nama . " (ID: " . $d->id . ") [Tipe: " . $d->tipe . "]\n";
}

$programs = \App\Models\Program::all();
echo "\nDaftar Program:\n";
foreach($programs as $p) {
    echo "- " . $p->nama_program . " (Unit ID: " . $p->unit_organisasi_id . ")\n";
}

$roles = \App\Models\User::whereHas('role', function($q) { $q->where('nama_role', 'pengurus_daerah'); })->get();
echo "\nPengurus Daerah Users:\n";
foreach($roles as $u) {
    echo "- " . $u->nama . " (Unit ID: " . $u->unit_organisasi_id . ")\n";
}
