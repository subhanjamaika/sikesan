<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

$user = \App\Models\User::whereHas('roles', function($q) {
    $q->where('nama_role', 'like', '%daerah%');
})->first();

if (!$user) {
    echo "User not found\n";
    exit;
}

echo "User: " . $user->name . "\n";
echo "Roles: " . implode(', ', $user->roles->pluck('nama_role')->toArray()) . "\n";

$allowedUnitIds = [];
$scopeMode = 'unit';

if ($user->hasAnyRole(['admin', 'dewan_pemangku'])) {
    $allowedUnitIds = \App\Models\UnitOrganisasi::pluck('id')->toArray();
    $scopeMode = 'all';
    echo "Matched Admin/Dewan\n";
} elseif ($user->hasAnyRole(['pengurus_wilayah', 'pengurus_daerah', 'pengurus_sektor', 'daerah', 'sektor'])) {
    echo "Matched Pengurus Wilayah/Daerah/Sektor\n";
    if ($user->unitOrganisasi) {
        $allowedUnitIds = $user->unitOrganisasi->getAllChildIds();
        echo "Unit Organisasi from user: " . count($allowedUnitIds) . " units\n";
    } else {
        echo "User unitOrganisasi is NULL\n";
    }

    $penugasan = $user->penugasanDaerah()->get();
    echo "Penugasan count: " . $penugasan->count() . "\n";
    foreach ($penugasan as $unit) {
        $allowedUnitIds = array_merge($allowedUnitIds, $unit->getAllChildIds());
    }

    $allowedUnitIds = array_values(array_unique($allowedUnitIds));
    $scopeMode = 'unit';
} else {
    echo "No Role Matched in ScopeWilayah!\n";
}

echo "Allowed Unit IDs: " . implode(', ', $allowedUnitIds) . "\n";
