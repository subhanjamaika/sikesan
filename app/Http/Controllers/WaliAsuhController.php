<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKegiatan;
use App\Models\AbsensiPuasa;
use App\Models\Pelanggaran;
use App\Models\PenilaianKeseharian;
use App\Models\Santri;
use Illuminate\Http\Request;

class WaliAsuhController extends Controller
{
    /**
     * Halaman santri asuhan (read-only) untuk role Wali Asuh.
     * Termasuk tab ke riwayat penilaian keseharian dan absensi.
     */
    public function santriAsuhan(Request $request)
    {
        $user = $request->user();
        $santriQuery = Santri::query();

        if ($user->hasRole('admin')) {
            // Admin: dapat melihat seluruh data santri
        } elseif ($user->hasRole('pengurus_sektor')) {
            // Wali Asuh: HANYA santri yang berada di kamar asuhannya (bisa beberapa kamar sekaligus)
            $kamarIds = $user->getAllowedUnitIds();
            if (!empty($kamarIds)) {
                $santriQuery->whereIn('unit_organisasi_id', $kamarIds);
            } else {
                $santriQuery->whereRaw('1 = 0');
            }
        } else {
            $santriQuery->whereRaw('1 = 0');
        }

        $santriAsuhan = $santriQuery->with('unitOrganisasi')->get();
        $santriIds = $santriAsuhan->pluck('id');

        // Riwayat absensi kegiatan
        $absensiKegiatan = AbsensiKegiatan::with(['santri', 'jenisKegiatan', 'pencatat'])
            ->whereIn('santri_id', $santriIds)
            ->latest('tanggal')
            ->get();

        // Riwayat absensi puasa
        $absensiPuasa = AbsensiPuasa::with(['santri', 'pencatat'])
            ->whereIn('santri_id', $santriIds)
            ->latest('tanggal')
            ->get();

        // Riwayat pelanggaran
        $pelanggaran = Pelanggaran::with(['santri', 'pencatat', 'pembinaan'])
            ->whereIn('santri_id', $santriIds)
            ->latest('tanggal')
            ->get();

        // Riwayat penilaian keseharian
        $penilaian = PenilaianKeseharian::with('santri')
            ->whereIn('santri_id', $santriIds)
            ->latest('tanggal_penilaian')
            ->get();

        return view('wali-asuh.santri-asuhan', compact(
            'santriAsuhan', 'absensiKegiatan', 'absensiPuasa', 'pelanggaran', 'penilaian'
        ));
    }
}
