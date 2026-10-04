<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$santris = \App\Models\Santri::with('unitOrganisasi.parent')->get();
$fixed = 0;

foreach($santris as $s) {
    if ($s->unitOrganisasi && $s->unitOrganisasi->parent) {
        $daerahName = $s->unitOrganisasi->parent->nama;
        $validPrograms = \App\Enums\ProgramEnum::getProgramIdsForDaerah($daerahName);
        
        if (!in_array($s->program_id->value ?? $s->program_id, $validPrograms)) {
            if ($daerahName === "Amtsilati") {
                $s->program_id = 'wustho_kitab';
                $s->save();
                $fixed++;
                echo "Fixed " . $s->nama . " to wustho_kitab\n";
            }
        }
    }
}
echo "Total fixed: $fixed\n";
