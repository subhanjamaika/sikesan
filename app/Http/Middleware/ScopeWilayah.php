<?php

namespace App\Http\Middleware;

use App\Models\UnitOrganisasi;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScopeWilayah
{
    /**
     * Handle an incoming request.
     * Mencegat request dan menetapkan scope wilayah berdasarkan Role user SSO.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $allowedUnitIds = [];
        $allowedSantriIds = [];
        $scopeMode = 'unit'; // 'unit', 'santri', atau 'all'

        // 1. Admin / Dewan Pemangku
        if ($user->hasAnyRole(['admin', 'dewan_pemangku'])) {
            $allowedUnitIds = UnitOrganisasi::pluck('id')->toArray();
            $scopeMode = 'all';
        }
        // 2. Pengurus Wilayah / Daerah / Sektor
        elseif ($user->hasAnyRole(['pengurus_wilayah', 'pengurus_daerah', 'pengurus_sektor', 'daerah', 'sektor'])) {
            if ($user->unitOrganisasi) {
                $allowedUnitIds = $user->unitOrganisasi->getAllChildIds();
            }

            // Tambahkan juga dari penugasan spesifik jika ada
            $penugasan = $user->penugasanDaerah()->get();
            foreach ($penugasan as $unit) {
                $allowedUnitIds = array_merge($allowedUnitIds, $unit->getAllChildIds());
            }

            $allowedUnitIds = array_values(array_unique($allowedUnitIds));
            $scopeMode = 'unit';
        }
        // 3. Wali Santri / Santri
        elseif ($user->hasAnyRole(['wali_santri', 'santri'])) {
            $scopeMode = 'santri';
            if ($user->santri_id) {
                $allowedSantriIds[] = $user->santri_id;
            }
        }

        // Simpan ke attributes agar bisa dibaca dari Request di Controller/FormRequest
        $request->attributes->set('allowedUnitIds', $allowedUnitIds);
        $request->attributes->set('allowedSantriIds', $allowedSantriIds);
        $request->attributes->set('scopeMode', $scopeMode);

        return $next($request);
    }
}
