<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$s = \App\Models\Santri::all();
echo 'Total Santri: ' . count($s) . "\n";
$s_wustho = \App\Models\Santri::where('program_id', 'wustho_tahsin')->count();
echo 'Wustho Tahsin: ' . $s_wustho . "\n";

$s_uuid = 0;
foreach($s as $st) {
    if ($st->getRawOriginal('program_id') && strpos($st->getRawOriginal('program_id'), '-') !== false) {
        $s_uuid++;
    }
}
echo 'UUID still there: ' . $s_uuid . "\n";

foreach($s as $st) {
    echo $st->nama . " - " . $st->getRawOriginal('program_id') . "\n";
}
