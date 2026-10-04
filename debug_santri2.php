<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function testUser($email) {
    $user = App\Models\User::where('email', $email)->first();
    if (!$user) {
        echo "$email NOT FOUND\n";
        return;
    }
    
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
    
    echo "$email: Santri Count = " . $query->count() . "\n";
}

testUser('admin@kewaliasuhan.test');
testUser('pengurus.wilayah@kewaliasuhan.test');
testUser('pengurus.bkwa@kewaliasuhan.test');
testUser('wali.asuh@kewaliasuhan.test'); // Sifir 01
testUser('kamtib@kewaliasuhan.test'); // Kamtib
