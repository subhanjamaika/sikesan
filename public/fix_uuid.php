<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$uuids = DB::table('santri')->where('program_id', 'like', '%-%')->distinct()->pluck('program_id');

foreach($uuids as $u) {
    $santri = \App\Models\Santri::with('unitOrganisasi.parent')->where('program_id', $u)->first();
    if ($santri && $santri->unitOrganisasi && $santri->unitOrganisasi->parent) {
        $daerahName = $santri->unitOrganisasi->parent->nama;
        $enumVal = null;
        if ($daerahName === "Tahsinul Qiro'ah") $enumVal = 'wustho_tahsin';
        if ($daerahName === "Amtsilati") $enumVal = 'wustho_kitab';
        if ($daerahName === "Excellent Language") $enumVal = 'wustho_bahasa';
        if ($daerahName === "Tahfidzul Qur'an") $enumVal = 'wustho_tahfidz';
        if ($daerahName === "Al-Mahfudzi") $enumVal = 'al_mahfudzi';
        if ($daerahName === "I'dadiyah") $enumVal = 'sifir';
        
        if ($enumVal) {
            DB::table('santri')->where('program_id', $u)->update(['program_id' => $enumVal]);
            echo "Updated $u to $enumVal\n";
        }
    }
}
