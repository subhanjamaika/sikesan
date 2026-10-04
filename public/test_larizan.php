<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$data = \App\Services\LarizanApiService::fetchSantriIzinAktif();
echo "Data dari Larizan:\n";
print_r($data);
