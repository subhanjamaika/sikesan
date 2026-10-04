<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

$kamars = \App\Services\CoreApiService::fetchKamar();
if (empty($kamars)) {
    echo "NO KAMAR RETURNED\n";
} else {
    print_r(array_slice($kamars, 0, 2));
}
