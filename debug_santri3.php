<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function testCreate($email) {
    $user = App\Models\User::where('email', $email)->first();
    if (!$user) {
        return;
    }
    
    $santriQuery = App\Models\Santri::query();
    if ($user->hasRole('wali_asuh')) {
        if ($user->daerah_binaan_id) {
            $santriQuery->whereHas('unitOrganisasi', function($q) use ($user) {
                $q->where('parent_id', $user->daerah_binaan_id)->orWhere('id', $user->daerah_binaan_id);
            });
        } else {
            $santriQuery->whereIn('unit_organisasi_id', $user->getKamarAsuhanIds());
        }
    } elseif ($user->hasRole('kabag_daerah')) {
        $santriQuery->whereIn('unit_organisasi_id', $user->getUnitScope());
    }

    echo "$email CREATE: Santri Count = " . $santriQuery->count() . "\n";
}

testCreate('admin@kewaliasuhan.test');
testCreate('wali.asuh@kewaliasuhan.test'); // Sifir 01
