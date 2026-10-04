<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

echo "API Unit Organisasi:\n";
$units = \App\Services\CoreApiService::fetchUnitOrganisasi();
print_r(array_slice($units, 0, 1));

echo "\nAPI Kamar:\n";
$kamars = \App\Services\CoreApiService::fetchKamar();
print_r(array_slice($kamars, 0, 1));

echo "\nAPI Program:\n";
$programs = \App\Services\CoreApiService::fetchProgram();
print_r(array_slice($programs, 0, 1));

echo "\nAPI Santri:\n";
$santris = \App\Services\CoreApiService::fetchSantri(['status' => 'aktif', 'per_page' => 1]);
print_r($santris);
