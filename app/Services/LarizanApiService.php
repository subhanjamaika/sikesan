<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LarizanApiService
{
    /**
     * Fetch active 'izin' santri IDs from Larizan API.
     *
     * @return array
     */
    public static function fetchSantriIzinAktif(array $santriIds = [], string $waktuMulai = null, string $waktuSelesai = null): array
    {
        $mapped = [];
        if (empty($santriIds) || empty($waktuMulai) || empty($waktuSelesai)) {
            return $mapped;
        }

        try {
            $baseUrl  = config('services.larizan.url', env('LARIZAN_API_URL', 'https://perizinan.zabitsa.site'));
            $endpoint = $baseUrl . '/api/izin-keluar';
            
            $headers = [
                'X-Service-Key' => env('LARIZAN_SERVICE_KEY'),
                'Accept'        => 'application/json',
            ];

            $chunks = array_chunk($santriIds, 200);

            foreach ($chunks as $chunk) {
                $query = [
                    'dari'        => $waktuMulai,
                    'sampai'      => $waktuSelesai,
                    'santri_ids'  => $chunk,
                ];

                $response = Http::withHeaders($headers)
                    ->timeout(5)           // Hindari hang jika server Larizan lambat
                    ->connectTimeout(3)    // Hindari hang jika server tidak dapat dijangkau
                    ->get($endpoint, $query);

                if ($response->successful()) {
                    $data = $response->json('data') ?? [];
                    
                    foreach ($data as $item) {
                        if (is_array($item) && isset($item['santri_id'])) {
                            $id = $item['santri_id'];
                            $jenis = strtolower($item['jenis'] ?? 'izin');
                            
                            $status = str_contains($jenis, 'sakit') ? 'sakit' : 'izin';
                            
                            $jenisLabel = $item['jenis_label'] ?? ucwords(str_replace('_', ' ', $jenis));
                            $statusLabel = $item['status_label'] ?? '';
                            
                            $keterangan = "Larizan: $jenisLabel";
                            if ($statusLabel) {
                                $keterangan .= " ($statusLabel)";
                            }

                            $mapped[$id] = [
                                'status' => $status,
                                'keterangan' => $keterangan
                            ];
                        }
                    }
                } elseif ($response->status() === 404) {
                    // FALLBACK: Endpoint /api/izin-keluar belum di-deploy di server Larizan.
                    // Gunakan /api/keterlambatan/aktif sementara (hanya menangkap santri yang sudah terlambat kembali).
                    Log::warning('Larizan API: endpoint /api/izin-keluar mengembalikan 404. '
                        . 'Kemungkinan belum di-deploy. Fallback ke /api/keterlambatan/aktif.');

                    $fallbackEndpoint = rtrim($baseUrl, '/') . '/api/keterlambatan/aktif';
                    $fallbackResponse = Http::withHeaders($headers)
                        ->timeout(5)
                        ->connectTimeout(3)
                        ->get($fallbackEndpoint);
                    
                    if ($fallbackResponse->successful()) {
                        $fallbackData = $fallbackResponse->json('data') ?? [];
                        foreach ($fallbackData as $item) {
                            if (is_array($item) && isset($item['santri_id'])) {
                                $id = $item['santri_id'];
                                // Hanya filter santri yang ada di chunk ini
                                if (!in_array($id, $chunk)) continue;
                                
                                $jenis = strtolower($item['jenis'] ?? 'izin');
                                $status = str_contains($jenis, 'sakit') ? 'sakit' : 'izin';
                                $jenisLabel = ucwords(str_replace('_', ' ', $jenis));
                                $lamaTerlambat = $item['lama_terlambat_label'] ?? '';
                                
                                $keterangan = "Larizan: Belum Kembali ($jenisLabel)";
                                if ($lamaTerlambat) {
                                    $keterangan .= " - Telat $lamaTerlambat";
                                }

                                $mapped[$id] = [
                                    'status'     => $status,
                                    'keterangan' => $keterangan,
                                ];
                            }
                        }
                    } else {
                        Log::error('Larizan API fallback keterlambatan/aktif juga gagal: '
                            . $fallbackResponse->status() . ' ' . $fallbackResponse->body());
                    }
                } else {
                    Log::error('Larizan API izin-keluar gagal: HTTP ' . $response->status() . ' — ' . $response->body());
                }
            }
        } catch (\Exception $e) {
            Log::error('Larizan API fetchSantriIzinAktif failed: ' . $e->getMessage());
        }

        return $mapped;
    }
}
