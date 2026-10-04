<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

$santri = \App\Models\Santri::where('nama', 'like', '%Azhar Kholili%')->first();
if (!$santri) {
    echo "Santri not found\n";
    exit;
}
echo "Found Santri: " . $santri->nama . " (" . $santri->id . ")\n";

$dari = \Carbon\Carbon::parse('2026-09-29', 'Asia/Jakarta')->startOfDay()->toIso8601String();
$sampai = \Carbon\Carbon::parse('2026-09-29', 'Asia/Jakarta')->endOfDay()->toIso8601String();

echo "Testing from $dari to $sampai\n";

$data = \App\Services\LarizanApiService::fetchSantriIzinAktif([$santri->id], $dari, $sampai);
print_r($data);
