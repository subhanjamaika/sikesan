<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());
use Illuminate\Support\Facades\Http;

$baseUrl = 'https://perizinan.zabitsa.site';
$headers = ['X-Service-Key' => env('LARIZAN_SERVICE_KEY'), 'Accept' => 'application/json'];

echo "Testing /api/keterlambatan/aktif\n";
$resp1 = Http::withHeaders($headers)->get($baseUrl . '/api/keterlambatan/aktif');
echo "Status: " . $resp1->status() . "\n";
echo "Data: " . substr($resp1->body(), 0, 150) . "\n\n";

echo "Testing /api/izin-keluar\n";
$resp2 = Http::withHeaders($headers)->get($baseUrl . '/api/izin-keluar');
echo "Status: " . $resp2->status() . "\n";
echo "Data: " . substr($resp2->body(), 0, 150) . "\n\n";

echo "Testing /api/izin-keluar with query\n";
$resp3 = Http::withHeaders($headers)->get($baseUrl . '/api/izin-keluar', ['dari' => '2026-09-29T00:00:00+07:00', 'sampai' => '2026-10-03T23:59:59+07:00', 'santri_ids' => ['019fcd5a-77a1-7314-87fb-eb106a6c765f']]);
echo "Status: " . $resp3->status() . "\n";
echo "Data: " . substr($resp3->body(), 0, 150) . "\n\n";

