<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Santri;
use Illuminate\Support\Facades\DB;

DB::table('santri')->truncate();

$id = '019fa1a4-c3bd-715c-8848-6a5a16b51af1';
Santri::create([
    'id' => $id,
    'nama' => 'Test Santri',
    'nis_induk' => '12345',
]);

echo "After insert: " . Santri::count() . "\n";

$santriIds = [$id];
Santri::whereNotIn('id', $santriIds)->delete();

echo "After whereNotIn: " . Santri::count() . "\n";
