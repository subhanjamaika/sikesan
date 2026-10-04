<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKegiatan;
use App\Models\AbsensiPuasa;
use App\Models\Pelanggaran;
use App\Models\PenilaianKeseharian;
use App\Models\Santri;
use Illuminate\Http\Request;

class WaliSantriController extends Controller
{
    /**
     * Riwayat anak untuk role Wali Santri (read-only).
     * Auto-filter berdasarkan santri_id pada user.
     */
    public function riwayatIzin(Request $request)
    {
        $user = $request->user();
        $santriId = $user->santri_id ?? $user->akses_wali_santri_id;

        // Auto-link santri jika belum terhubung
        if (!$santriId) {
            $firstSantri = Santri::first();
            if ($firstSantri) {
                $user->update([
                    'santri_id'            => $firstSantri->id,
                    'akses_wali_santri_id' => $firstSantri->id,
                ]);
                $santriId = $firstSantri->id;
            }
        }

        $santriWali = $santriId ? Santri::where('id', $santriId)->get() : collect();

        // Riwayat Kehadiran (Grouped by jenis_kegiatan_id)
        $absensi = AbsensiKegiatan::with('jenisKegiatan')
            ->where('santri_id', $santriId)
            ->get();

        $riwayatKehadiran = $absensi->groupBy('jenis_kegiatan_id')->map(function ($items, $kegiatanId) {
            $kegiatan = $items->first()->jenisKegiatan;
            $hadir = $items->where('status', 'hadir')->count();
            $izin = $items->where('status', 'izin')->count();
            $sakit = $items->where('status', 'sakit')->count();
            $alpa = $items->where('status', 'alpa')->count();
            $total = $items->count();

            return (object) [
                'id' => $kegiatan->id ?? null,
                'nama_kegiatan' => $kegiatan->nama_kegiatan ?? 'Kegiatan Terhapus',
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'total' => $total,
                'persentase' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
                'detail' => $items->sortByDesc('tanggal')->values()
            ];
        })->values();

        // Riwayat absensi puasa anak
        $absensiPuasa = AbsensiPuasa::with(['santri', 'pencatat'])
            ->where('santri_id', $santriId)
            ->latest('tanggal')
            ->get();

        // Riwayat penilaian keseharian anak
        $penilaian = PenilaianKeseharian::with(['santri', 'waliAsuh'])
            ->where('santri_id', $santriId)
            ->latest('tanggal_penilaian')
            ->get();

        // Riwayat pelanggaran anak (Dikunci mutlak untuk keamanan data)
        $pelanggaran = Pelanggaran::with(['santri', 'pencatat', 'pembinaan'])
            ->where('santri_id', $santriId)
            ->latest('tanggal')
            ->get();

        return view('wali-santri.riwayat-izin', compact(
            'santriWali', 'riwayatKehadiran', 'absensiPuasa', 'penilaian', 'pelanggaran'
        ));
    }
}
