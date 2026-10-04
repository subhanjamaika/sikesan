<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$santris = \App\Models\Santri::with('unitOrganisasi.parent')->get();
$invalidAmtsilati = 0;
$invalidExcellent = 0;

foreach($santris as $s) {
    if ($s->unitOrganisasi && $s->unitOrganisasi->parent) {
        $daerahName = $s->unitOrganisasi->parent->nama;
        $validPrograms = \App\Enums\ProgramEnum::getProgramIdsForDaerah($daerahName);
        
        if (!in_array($s->program_id->value ?? $s->program_id, $validPrograms)) {
            if ($daerahName === "Amtsilati") $invalidAmtsilati++;
            if ($daerahName === "Excellent Language") $invalidExcellent++;
        }
    }
}
echo "Invalid Amtsilati: $invalidAmtsilati\n";
echo "Invalid Excellent Language: $invalidExcellent\n";
