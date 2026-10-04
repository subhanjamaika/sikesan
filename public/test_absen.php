<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Santri count per program_id in DB:\n";
$counts = \App\Models\Santri::groupBy('program_id')->selectRaw('program_id, count(*) as count')->get();
foreach ($counts as $c) {
    echo ($c->program_id ? $c->program_id->value : 'null') . " : " . $c->count . "\n";
}
