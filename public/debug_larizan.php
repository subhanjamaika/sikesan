<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\Http;

echo "==========================================================\n";
echo "    DEBUGGING REQUEST KE LARIZAN (API IZIN KELUAR)\n";
echo "==========================================================\n\n";

$baseUrl = env('LARIZAN_API_URL', 'https://perizinan.zabitsa.site');
$endpoint = $baseUrl . '/api/izin-keluar';

// Simulasi parameter persis seperti yang akan dikirim Sikesan untuk absensi
$query = [
    'dari' => \Carbon\Carbon::now()->startOfDay()->toIso8601String(),
    'sampai' => \Carbon\Carbon::now()->endOfDay()->toIso8601String(),
    // Contoh 1 Santri (UUID)
    'santri_ids' => ['019fcd5a-77a1-7314-87fb-eb106a6c765f']
];

$headers = [
    'X-Service-Key' => env('LARIZAN_SERVICE_KEY'),
    'Accept' => 'application/json'
];

echo "[1] MEMPERSIAPKAN REQUEST...\n";
echo "----------------------------------------------------------\n";
echo "URL Tujuan    : " . $endpoint . "\n";
echo "Method        : GET\n";
echo "Header Dikirim: \n";
echo "   - Accept: application/json\n";
echo "   - X-Service-Key: (Dirahasiakan)\n";
echo "Query String  : \n";
foreach ($query as $key => $value) {
    if (is_array($value)) {
        echo "   - $key = [" . implode(', ', $value) . "]\n";
    } else {
        echo "   - $key = $value\n";
    }
}
echo "\n";

echo "[2] MENGIRIM REQUEST KE SERVER LARIZAN...\n";
echo "----------------------------------------------------------\n";

try {
    $resp = Http::withHeaders($headers)->get($endpoint, $query);

    // Dapatkan URL Aktual (dengan query string yang sudah di-build)
    echo "URL Aktual yg Dipanggil : " . $resp->effectiveUri() . "\n\n";

    echo "[3] HASIL RESPONSE DARI SERVER LARIZAN:\n";
    echo "----------------------------------------------------------\n";
    echo "HTTP Status Code : " . $resp->status() . " ";
    
    if ($resp->status() === 404) {
        echo "(Not Found / Tidak Ditemukan)  <--- BUKTI ERROR!\n";
    } else {
        echo "\n";
    }

    echo "Isi Response     :\n";
    
    // Potong response body sedikit jika Nginx HTML supaya tidak penuhi layar
    $body = $resp->body();
    if (str_contains($body, '<html>')) {
        echo substr($body, 0, 200) . " ... (terpotong, format HTML Nginx)\n";
    } else {
        echo $body . "\n";
    }

    echo "\n==========================================================\n";
    echo "KESIMPULAN DEBUGGING:\n";
    if ($resp->status() === 404) {
        echo "=> Server LARIZAN gagal mengenali URL /api/izin-keluar.\n";
        echo "=> Artinya kode API tersebut BELUM ADA di server production Larizan.\n";
    }

} catch (\Exception $e) {
    echo "ERROR SISTEM: " . $e->getMessage() . "\n";
}
echo "==========================================================\n";
