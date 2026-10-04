<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$santris = \App\Models\Santri::limit(10)->get();
echo "Sample Santri:\n";
foreach($santris as $s) {
    echo "- " . $s->nama . " (Unit ID: " . $s->unit_organisasi_id . ", Program ID: " . $s->program_id . ")\n";
}

$counts = \Illuminate\Support\Facades\DB::table('santri')->select('unit_organisasi_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))->groupBy('unit_organisasi_id')->get();
echo "\nSantri counts by unit_organisasi_id:\n";
foreach($counts as $c) {
    echo "- Unit: " . $c->unit_organisasi_id . " => " . $c->total . "\n";
}
