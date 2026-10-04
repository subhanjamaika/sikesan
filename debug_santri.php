<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'kabag.daerah@kewaliasuhan.test')->first();
$query = App\Models\Santri::query();

if ($user->hasRole('wali_asuh')) {
    if ($user->daerah_binaan_id) {
        $query->whereHas('unitOrganisasi', function($q) use ($user) {
            $q->where('parent_id', $user->daerah_binaan_id)->orWhere('id', $user->daerah_binaan_id);
        });
    } else {
        $query->whereIn('unit_organisasi_id', $user->getKamarAsuhanIds());
    }
} elseif ($user->hasRole(['kabag_daerah', 'pengurus_wilayah'])) {
    $unitScope = $user->getUnitScope();
    if (!empty($unitScope)) {
        $query->whereIn('unit_organisasi_id', $unitScope);
    }
}

echo "Kabag Daerah Sifir Santri Count: " . $query->count() . "\n";
echo "getUnitScope: " . json_encode($user->getUnitScope()) . "\n";
echo "hasRole: " . json_encode($user->hasRole(['kabag_daerah', 'pengurus_wilayah'])) . "\n";
