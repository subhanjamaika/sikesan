<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\Http;

$baseUrl = 'https://perizinan.zabitsa.site';
$headers = ['X-Service-Key' => env('LARIZAN_SERVICE_KEY'), 'Accept' => 'application/json'];

$resp = Http::withHeaders($headers)->get($baseUrl . '/api/asdfgasdfg');
file_put_contents(__DIR__ . '/larizan_404.html', $resp->body());
echo "Saved to larizan_404.html\n";
