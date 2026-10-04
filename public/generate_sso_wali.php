<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(Illuminate\Http\Request::capture());

$santri = \App\Models\Santri::inRandomOrder()->first();

if ($santri) {
    $namaWali = "Bapak/Ibu dari " . $santri->nama;
    $uniqueUsername = 'wali_' . strtolower(preg_replace('/[^a-zA-Z]/', '', explode(' ', $santri->nama)[0])) . '_' . substr($santri->id, 0, 5);
    // Kita gunakan sebagian dari UUID Santri sebagai user_id (agar jika login ulang dengan anak yang sama, ID user-nya tetap sama)
    $deterministicUserId = 'wali-' . substr($santri->id, 5); 
    
    $ssoUrl = url('/dev/sso-login?role=wali_santri&user_id=' . $deterministicUserId . '&username=' . $uniqueUsername . '&nama=' . urlencode($namaWali) . '&santri_id=' . $santri->id);
    
    echo "<h1>Data Santri Real Ditemukan!</h1>";
    echo "<ul>";
    echo "<li><strong>Nama Anak:</strong> {$santri->nama}</li>";
    echo "<li><strong>Santri ID:</strong> {$santri->id}</li>";
    echo "</ul>";
    
    echo "<h3>Klik Link di bawah ini untuk Login Dummy SSO sebagai Wali dari anak ini:</h3>";
    echo "<a href='{$ssoUrl}' style='display:inline-block; padding:10px 20px; background:blue; color:white; text-decoration:none; border-radius:5px;'>Login sebagai Wali Santri</a>";
} else {
    echo "Tidak ada data santri di database. Silakan jalankan seeder atau sinkronisasi data terlebih dahulu.";
}
