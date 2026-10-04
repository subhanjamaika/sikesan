<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SsoSyncController extends Controller
{
    /**
     * Endpoint untuk menerima webhook sinkronisasi data santri dari SSO Zabitsa.
     * Menggunakan PATCH agar field wali_asuh_id yang unik di sistem kewaliasuhan
     * tidak tertimpa/terhapus menjadi NULL.
     */
    public function sync(Request $request)
    {
        // Validasi payload dasar (sesuaikan dengan format payload SSO)
        $validated = $request->validate([
            'id' => 'required|uuid',
            'nama' => 'required|string',
            'nis_induk' => 'required|string',
            'unit_organisasi_id' => 'nullable|uuid|exists:unit_organisasi,id',
            'wali_santri_kontak' => 'nullable|string',
            // jangan paksa menerima wali_asuh_id dari SSO
        ]);

        try {
            $santri = Santri::find($validated['id']);

            if ($santri) {
                // Update atribut tanpa menyentuh wali_asuh_id
                $santri->update([
                    'nama' => $validated['nama'],
                    'nis_induk' => $validated['nis_induk'],
                    'unit_organisasi_id' => $validated['unit_organisasi_id'] ?? $santri->unit_organisasi_id,
                    'wali_santri_kontak' => $validated['wali_santri_kontak'] ?? $santri->wali_santri_kontak,
                ]);
                $status = 'updated';
            } else {
                // Create baru
                $santri = Santri::create([
                    'id' => $validated['id'],
                    'nama' => $validated['nama'],
                    'nis_induk' => $validated['nis_induk'],
                    'unit_organisasi_id' => $validated['unit_organisasi_id'] ?? null,
                    'wali_santri_kontak' => $validated['wali_santri_kontak'] ?? null,
                    'wali_asuh_id' => null, // akan di-set secara manual di dashboard kewaliasuhan
                ]);
                $status = 'created';
            }

            return response()->json([
                'message' => "Data santri berhasil di-{$status}.",
                'data' => $santri
            ]);
        } catch (\Exception $e) {
            Log::error('SSO Sync Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat sinkronisasi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
