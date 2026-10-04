<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\Http;

$baseUrl = 'https://perizinan.zabitsa.site';
$headers = [
    'X-Service-Key' => env('LARIZAN_SERVICE_KEY'),
    'Accept'        => 'application/json',
];

$endpoints = [
    '/api/pengajuan/berjalan',
    '/api/pengajuan/disetujui',
    '/api/izin/berjalan',
    '/api/izin/berlangsung',
    '/api/perizinan/berjalan'
];

foreach ($endpoints as $ep) {
    echo "Testing $ep...\n";
    $resp = Http::withHeaders($headers)->get($baseUrl . $ep);
    if ($resp->successful()) {
        echo "SUCCESS: " . substr(json_encode($resp->json()), 0, 200) . "\n\n";
    } else {
        echo "FAILED: " . $resp->status() . "\n\n";
    }
}
