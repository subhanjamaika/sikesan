<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'admin@kewaliasuhan.test')->first();
$query = App\Models\Santri::query();

echo "Admin Santri count: " . $query->count() . "\n";
