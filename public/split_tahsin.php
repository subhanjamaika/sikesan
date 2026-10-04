<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$daerah = \App\Models\UnitOrganisasi::where('nama', "Tahsinul Qiro'ah")->first();
$kamars = \App\Models\UnitOrganisasi::where('parent_id', $daerah->id)->orderBy('nama')->get();

$half = floor($kamars->count() / 2);
$idadiyahKamars = $kamars->slice(0, $half)->pluck('id')->toArray();

$santris = \App\Models\Santri::whereIn('unit_organisasi_id', $kamars->pluck('id'))->get();
$updated = 0;

foreach($santris as $s) {
    if (in_array($s->unit_organisasi_id, $idadiyahKamars)) {
        $s->program_id = 'idadiyah';
    } else {
        $s->program_id = 'wustho_tahsin';
    }
    $s->save();
    $updated++;
}
echo "Updated $updated santri in Tahsinul Qiro'ah.\n";
