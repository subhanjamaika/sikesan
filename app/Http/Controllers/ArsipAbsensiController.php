<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKegiatan;
use App\Models\AbsensiPuasa;
use App\Models\JenisKegiatan;
use App\Models\Santri;
use App\Models\UnitOrganisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ArsipAbsensiController extends Controller
{
    /**
     * Level 1 — Daftar Periode Arsip (Card Grid: "Agustus 2026", dll).
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', AbsensiKegiatan::class);

        $user = $request->user();
        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);
        $scopeMode = $request->attributes->get('scopeMode', 'unit');
        $showDaerahFilter = $user->hasAnyRole(['admin', 'pengurus_wilayah', 'dewan_pemangku']);

        $daerahId = $request->get('daerah_id');

        // Ambil santri IDs sesuai scope
        $santriIdsQuery = Santri::query();
        if ($scopeMode === 'santri') {
            $santriIdsQuery->where('id', $user->santri_id);
        } elseif ($scopeMode !== 'all' && !empty($allowedUnitIds)) {
            $santriIdsQuery->whereIn('unit_organisasi_id', $allowedUnitIds);
        }
        if ($daerahId) {
            $childIds = UnitOrganisasi::where('parent_id', $daerahId)->pluck('id')->toArray();
            $santriIdsQuery->whereIn('unit_organisasi_id', array_merge([$daerahId], $childIds));
        }
        $santriIds = $santriIdsQuery->pluck('id');

        // Ambil periode unik yang sudah diarsipkan
        $periodes = AbsensiKegiatan::where('is_archived', true)
            ->whereIn('santri_id', $santriIds)
            ->selectRaw("MONTH(tanggal) as bulan, YEAR(tanggal) as tahun, COUNT(*) as total_record")
            ->selectRaw("SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as total_hadir")
            ->groupByRaw('YEAR(tanggal), MONTH(tanggal)')
            ->orderByRaw('YEAR(tanggal) DESC, MONTH(tanggal) DESC')
            ->get()
            ->map(function ($item) {
                $item->nama_bulan = date('F', mktime(0, 0, 0, $item->bulan, 10));
                $item->persentase = $item->total_record > 0 ? round(($item->total_hadir / $item->total_record) * 100, 1) : 0;
                return $item;
            });

        $daerahList = UnitOrganisasi::where('tipe', 'daerah')
            ->when($scopeMode !== 'all', fn($q) => $q->whereIn('id', $allowedUnitIds))
            ->orderBy('nama')
            ->get()
            ->unique('nama');

        return view('arsip.index', compact('periodes', 'daerahList', 'showDaerahFilter', 'daerahId'));
    }

    /**
     * Level 2 — Folder Kegiatan dalam satu periode arsip.
     */
    public function kegiatan(Request $request, $bulan, $tahun)
    {
        Gate::authorize('viewAny', AbsensiKegiatan::class);

        $user = $request->user();
        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);
        $scopeMode = $request->attributes->get('scopeMode', 'unit');
        $showDaerahFilter = $user->hasAnyRole(['admin', 'pengurus_wilayah', 'dewan_pemangku']);

        $daerahId = $request->get('daerah_id');

        // Ambil santri IDs sesuai scope
        $santriIdsQuery = Santri::query();
        if ($scopeMode === 'santri') {
            $santriIdsQuery->where('id', $user->santri_id);
        } elseif ($scopeMode !== 'all' && !empty($allowedUnitIds)) {
            $santriIdsQuery->whereIn('unit_organisasi_id', $allowedUnitIds);
        }
        if ($daerahId) {
            $childIds = UnitOrganisasi::where('parent_id', $daerahId)->pluck('id')->toArray();
            $santriIdsQuery->whereIn('unit_organisasi_id', array_merge([$daerahId], $childIds));
        }
        $santriIds = $santriIdsQuery->pluck('id');

        // Ambil kegiatan yang memiliki data arsip pada periode ini
        $jenisKegiatanIds = AbsensiKegiatan::where('is_archived', true)
            ->whereIn('santri_id', $santriIds)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->distinct()
            ->pluck('jenis_kegiatan_id');

        $jenisKegiatanList = JenisKegiatan::withoutGlobalScopes()->whereIn('id', $jenisKegiatanIds)->get();

        $kegiatanStats = [];
        foreach ($jenisKegiatanList as $kegiatan) {
            $stats = AbsensiKegiatan::where('jenis_kegiatan_id', $kegiatan->id)
                ->whereIn('santri_id', $santriIds)
                ->where('is_archived', true)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                    SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
                    SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                    SUM(CASE WHEN status = 'alpa' THEN 1 ELSE 0 END) as alpa
                ")->first();

            $total = (int) $stats->total;
            $hadir = (int) $stats->hadir;
            $persentase = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;

            $kegiatanStats[] = (object) [
                'id' => $kegiatan->id,
                'nama_kegiatan' => $kegiatan->nama_kegiatan,
                'jam_pelaksanaan' => $kegiatan->jam_pelaksanaan,
                'hadir' => $hadir,
                'izin' => (int) $stats->izin,
                'sakit' => (int) $stats->sakit,
                'alpa' => (int) $stats->alpa,
                'total' => $total,
                'persentase' => $persentase,
            ];
        }

        $totalSantri = $santriIds->count();
        $totalHadirAll = collect($kegiatanStats)->sum('hadir');
        $totalPertemuanAll = collect($kegiatanStats)->sum('total');
        $totalAlpaAll = collect($kegiatanStats)->sum('alpa');
        $avgKehadiran = $totalPertemuanAll > 0 ? round(($totalHadirAll / $totalPertemuanAll) * 100, 1) : 0;

        $daerahList = UnitOrganisasi::where('tipe', 'daerah')
            ->when($scopeMode !== 'all', fn($q) => $q->whereIn('id', $allowedUnitIds))
            ->orderBy('nama')
            ->get()
            ->unique('nama');

        $namaBulan = date('F', mktime(0, 0, 0, $bulan, 10));

        return view('arsip.kegiatan', compact(
            'kegiatanStats', 'bulan', 'tahun', 'namaBulan',
            'totalSantri', 'totalAlpaAll', 'avgKehadiran',
            'daerahList', 'showDaerahFilter', 'daerahId'
        ));
    }

    /**
     * Level 3 — Detail per-santri untuk satu kegiatan arsip (JSON for Modal).
     */
    public function detailKegiatan(Request $request)
    {
        Gate::authorize('viewAny', AbsensiKegiatan::class);

        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');
        $kegiatanId = $request->get('kegiatan_id');

        if (!$bulan || !$tahun || !$kegiatanId) {
            return response()->json(['error' => 'Parameter tidak lengkap'], 400);
        }

        $user = $request->user();
        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);
        $scopeMode = $request->attributes->get('scopeMode', 'unit');

        $santriQuery = Santri::with(['unitOrganisasi.parent', 'absensiKegiatan' => function($q) use ($bulan, $tahun, $kegiatanId) {
            $q->where('jenis_kegiatan_id', $kegiatanId)
              ->where('is_archived', true)
              ->whereMonth('tanggal', $bulan)
              ->whereYear('tanggal', $tahun);
        }]);

        if ($scopeMode === 'santri') {
            $santriQuery->where('id', $user->santri_id);
        } elseif (!empty($allowedUnitIds)) {
            $santriQuery->whereIn('unit_organisasi_id', $allowedUnitIds);
        }

        $santriList = $santriQuery->orderBy('nama')->get();

        $result = $santriList->map(function ($santri) {
            $hadir = $santri->absensiKegiatan->where('status', 'hadir')->count();
            $izin = $santri->absensiKegiatan->where('status', 'izin')->count();
            $sakit = $santri->absensiKegiatan->where('status', 'sakit')->count();
            $alpa = $santri->absensiKegiatan->where('status', 'alpa')->count();
            $total = $hadir + $izin + $sakit + $alpa;

            return [
                'nama' => $santri->nama,
                'nis' => $santri->nis_induk,
                'daerah' => $santri->unitOrganisasi->parent->nama ?? '-',
                'kamar' => $santri->unitOrganisasi->nama ?? '-',
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'total' => $total,
                'persentase' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
            ];
        })->filter(fn($item) => $item['total'] > 0)->values();

        return response()->json(['data' => $result]);
    }
}
