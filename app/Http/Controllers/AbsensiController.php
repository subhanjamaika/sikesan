<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAbsensiKegiatanRequest;
use App\Http\Requests\StoreAbsensiPuasaRequest;
use App\Models\AbsensiKegiatan;
use App\Models\AbsensiPuasa;
use App\Models\JenisKegiatan;
use App\Models\Santri;
use App\Models\UnitOrganisasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Services\LarizanApiService;

class AbsensiController extends Controller
{
    /**
     * Halaman Utama Absensi Terpadu (Kegiatan Harian & Puasa).
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', AbsensiKegiatan::class); // Sama-sama butuh izin absensi

        $user = $request->user();
        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);
        $scopeMode = $request->attributes->get('scopeMode', 'unit');
        $showDaerahFilter = $user->hasAnyRole(['admin', 'pengurus_wilayah', 'dewan_pemangku']);

        // Parameter filter umum
        $tanggal = $request->get('tanggal', date('Y-m-d'));
        $daerahId = $request->get('daerah_id');
        $kamarId = $request->get('kamar_id');

        // Parameter khusus Kegiatan
        $kegiatanId = $request->get('kegiatan_id');
        $jenisKegiatanList = JenisKegiatan::where(function ($query) use ($allowedUnitIds) {
            $query->whereIn('unit_organisasi_id', $allowedUnitIds)
                  ->orWhereNull('unit_organisasi_id');
        })->where('aktif', true)->orderBy('nama_kegiatan', 'asc')->get();
        $selectedJenisKegiatanId = null;

        if ($kegiatanId) {
            $jk = $jenisKegiatanList->where('id', $kegiatanId)->first();
            if ($jk) {
                $selectedJenisKegiatanId = $jk->id;
            }
        } elseif (!$request->has('kegiatan_id') && $jenisKegiatanList->isNotEmpty()) {
            $jk = $jenisKegiatanList->first();
            $selectedJenisKegiatanId = $jk->id;
            $kegiatanId = $jk->id;
        }

        // Dropdown Daerah — scope-aware (dari database lokal)
        $daerahList = UnitOrganisasi::whereIn('tipe', ['daerah', 'daerah_pendidikan'])
            ->when($scopeMode !== 'all', fn($q) => $q->whereIn('id', $allowedUnitIds))
            ->orderBy('nama')
            ->get();

        $allowedDaerahIds = $daerahList->pluck('id')->toArray();
        
        // Daftar Kamar dari database lokal
        $kamarsForSelectedDaerah = UnitOrganisasi::whereIn('tipe', ['kamar', 'sektor'])
            ->when($daerahId, fn($q) => $q->where('parent_id', $daerahId))
            ->when($scopeMode !== 'all', fn($q) => $q->whereIn('parent_id', $allowedDaerahIds))
            ->orderBy('nama')
            ->get();

        // Ambil scope santri — unified via middleware
        $santriQuery = Santri::query();
        
        $currentKegiatan = null;
        if ($selectedJenisKegiatanId) {
            $currentKegiatan = $jenisKegiatanList->where('id', $selectedJenisKegiatanId)->first();
        }

        if ($scopeMode === 'santri') {
            $santriQuery->where('id', $user->santri_id);
        } else {
            // Override scope untuk Kegiatan Lokal khusus Admin/Superadmin
            if ($currentKegiatan && !is_null($currentKegiatan->unit_organisasi_id) && $user->hasAnyRole(['admin', 'superadmin'])) {
                $kegiatanUnitId = $currentKegiatan->unit_organisasi_id;
                $childIds = UnitOrganisasi::where('parent_id', $kegiatanUnitId)->pluck('id')->toArray();
                $santriQuery->whereIn('unit_organisasi_id', array_merge([$kegiatanUnitId], $childIds));
            } elseif ($scopeMode !== 'all') {
                $santriQuery->inUnitOrProgram($allowedUnitIds);
            }
        }

        // Filter tambahan dari Program Kegiatan (jika kegiatan memiliki program spesifik)
        if ($currentKegiatan && !is_null($currentKegiatan->program_id)) {
            $santriQuery->where('program_id', $currentKegiatan->program_id);
        }

        // Filter tambahan dari dropdown
        if ($daerahId && !$kamarId) {
            $childIds = UnitOrganisasi::where('parent_id', $daerahId)->pluck('id')->toArray();
            $santriQuery->whereIn('unit_organisasi_id', array_merge([$daerahId], $childIds));
        }
        if ($kamarId) {
            $santriQuery->where('unit_organisasi_id', $kamarId);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $santriQuery->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis_induk', 'like', "%{$search}%");
            });
        }

        $activeTab = $request->get('tab', 'kegiatan');
        
        // Logika Khusus Puasa Sunnah (jika tidak wajib, jangan load semua santri kecuali dicari / sudah ada datanya)
        if ($activeTab === 'puasa') {
            $selectedDaerah = null;
            if ($daerahId) {
                $selectedDaerah = $daerahList->where('id', $daerahId)->first();
            } elseif (!$showDaerahFilter && $daerahList->isNotEmpty()) {
                $selectedDaerah = $daerahList->first();
            }

            if ($selectedDaerah && !$selectedDaerah->is_puasa_wajib && !$request->filled('search')) {
                // Hanya load santri yang sudah punya record puasa hari ini (karena sunnah, tidak perlu tampilkan semua untuk diabsen)
                $santriQuery->whereHas('absensiPuasa', function($q) use ($tanggal) {
                    $q->where('tanggal', $tanggal)->where('is_archived', false);
                });
            }
        }

        $santriList = $santriQuery->orderBy('nama')->get();

        // Data Absensi Harian Existing (hanya data aktif, bukan arsip)
        $absensiKegiatanMap = [];
        if ($selectedJenisKegiatanId) {
            $absensiKegiatanMap = AbsensiKegiatan::where('jenis_kegiatan_id', $selectedJenisKegiatanId)
                ->where('tanggal', $tanggal)
                ->where('is_archived', false)
                ->get()
                ->keyBy('santri_id');
        }

        // Data Absensi Puasa Existing (hanya data aktif)
        $absensiPuasaMap = AbsensiPuasa::where('tanggal', $tanggal)
            ->where('is_archived', false)
            ->get()
            ->keyBy('santri_id');

        // Cek apakah tanggal saat ini adalah Senin atau Kamis
        $isSeninKamis = false;
        try {
            $parsedDate = Carbon::parse($tanggal);
            $isSeninKamis = $parsedDate->isMonday() || $parsedDate->isThursday();
        } catch (\Exception $e) {}

        $waktuMulai = Carbon::parse($tanggal)->startOfDay();
        $waktuSelesai = Carbon::parse($tanggal)->endOfDay();
        
        if ($activeTab === 'kegiatan' && $currentKegiatan && $currentKegiatan->jam_pelaksanaan) {
            try {
                $waktuMulai = Carbon::parse($tanggal . ' ' . $currentKegiatan->jam_pelaksanaan);
                $waktuSelesai = $waktuMulai->copy()->addHours(2);
            } catch (\Exception $e) {}
        }
        
        $santriIds = $santriList->pluck('id')->toArray();
        $santriIzinIds = LarizanApiService::fetchSantriIzinAktif($santriIds, $waktuMulai->toIso8601String(), $waktuSelesai->toIso8601String());

        return view('absensi.index', compact(
            'tanggal',
            'daerahList',
            'kamarsForSelectedDaerah',
            'santriList',
            'jenisKegiatanList',
            'selectedJenisKegiatanId',
            'kegiatanId',
            'absensiKegiatanMap',
            'absensiPuasaMap',
            'isSeninKamis',
            'showDaerahFilter',
            'santriIzinIds'
        ));
    }

    /**
     * Simpan absensi kegiatan harian.
     */
    public function store(StoreAbsensiKegiatanRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        $jenisKegiatanId = $validated['jenis_kegiatan_id'] ?? null;

        try {
            DB::beginTransaction();
            foreach ($validated['absensi'] as $item) {
                AbsensiKegiatan::updateOrCreate(
                    [
                        'santri_id'         => $item['santri_id'],
                        'jenis_kegiatan_id' => $jenisKegiatanId,
                        'tanggal'           => $validated['tanggal'],
                    ],
                    [
                        'status'      => $item['status'],
                        'keterangan'  => $item['keterangan'] ?? null,
                        'pencatat_id' => $user->id,
                    ]
                );
            }
            DB::commit();

            return redirect()->route('absensi.index', [
                'tanggal'           => $validated['tanggal'],
                'kegiatan_id'       => $jenisKegiatanId,
                'kamar_id'          => $request->get('kamar_id'),
                'tab'               => 'kegiatan',
                'show_table'        => 1
            ])->with('success', 'Berhasil menyimpan absensi kegiatan harian.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan absensi kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Simpan absensi puasa.
     */
    public function storePuasa(StoreAbsensiPuasaRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        try {
            DB::beginTransaction();
            foreach ($validated['absensi'] as $item) {
                AbsensiPuasa::updateOrCreate(
                    [
                        'santri_id' => $item['santri_id'],
                        'tanggal'   => $validated['tanggal'],
                    ],
                    [
                        'status_puasa' => $item['status_puasa'],
                        'keterangan'   => $item['keterangan'] ?? null,
                        'pencatat_id'  => $user->id,
                    ]
                );
            }
            DB::commit();

            return redirect()->route('absensi.index', [
                'tanggal'   => $validated['tanggal'],
                'kamar_id'  => $request->get('kamar_id'),
                'tab'       => 'puasa',
                'show_table'=> 1
            ])->with('success', 'Berhasil menyimpan absensi puasa.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan absensi puasa: ' . $e->getMessage());
        }
    }
    /**
     * Helper untuk mengambil data rekap — Folder Kegiatan Mode
     */
    private function getRekapData(Request $request)
    {
        $user = $request->user();
        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);
        $scopeMode = $request->attributes->get('scopeMode', 'unit');
        $showDaerahFilter = $user->hasAnyRole(['admin', 'pengurus_wilayah', 'dewan_pemangku']);

        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $daerahId = $request->get('daerah_id');
        $kamarId = $request->get('kamar_id');

        // Dropdown Daerah — scope-aware (dari database lokal)
        $daerahList = UnitOrganisasi::whereIn('tipe', ['daerah', 'daerah_pendidikan'])
            ->when($scopeMode !== 'all', fn($q) => $q->whereIn('id', $allowedUnitIds))
            ->orderBy('nama')
            ->get();

        $allowedDaerahIds = $daerahList->pluck('id')->toArray();
        
        // Daftar Kamar dari database lokal
        $kamarsForSelectedDaerah = UnitOrganisasi::whereIn('tipe', ['kamar', 'sektor'])
            ->when($daerahId, fn($q) => $q->where('parent_id', $daerahId))
            ->when($scopeMode !== 'all', fn($q) => $q->whereIn('parent_id', $allowedDaerahIds))
            ->orderBy('nama')
            ->get();

        // Ambil daftar kegiatan sesuai scope RBAC
        $jenisKegiatanList = JenisKegiatan::where(function ($query) use ($allowedUnitIds) {
            $query->whereIn('unit_organisasi_id', $allowedUnitIds)
                  ->orWhereNull('unit_organisasi_id');
        })->where('aktif', true)->orderBy('nama_kegiatan', 'asc')->get();

        // Bangun scope santri_id untuk filter daerah
        $santriIds = Santri::query()
            ->when($scopeMode === 'santri', fn($q) => $q->where('id', $user->santri_id))
            ->when($scopeMode !== 'santri' && $scopeMode !== 'all', fn($q) => $q->inUnitOrProgram($allowedUnitIds))
            ->when($daerahId && !$kamarId, function ($q) use ($daerahId) {
                $childIds = UnitOrganisasi::where('parent_id', $daerahId)->pluck('id')->toArray();
                $q->whereIn('unit_organisasi_id', array_merge([$daerahId], $childIds));
            })
            ->when($kamarId, function ($q) use ($kamarId) {
                $q->where('unit_organisasi_id', $kamarId);
            })
            ->pluck('id');

        $totalSantri = $santriIds->count();

        // Hitung statistik per-kegiatan (Folder Cards)
        $kegiatanStats = [];
        $totalHadirAll = 0;
        $totalAlpaAll = 0;
        $totalPertemuanAll = 0;

        foreach ($jenisKegiatanList as $kegiatan) {
            $stats = AbsensiKegiatan::where('jenis_kegiatan_id', $kegiatan->id)
                ->whereIn('santri_id', $santriIds)
                ->where('is_archived', false)
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
            $izin = (int) $stats->izin;
            $sakit = (int) $stats->sakit;
            $alpa = (int) $stats->alpa;
            $persentase = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;

            $kegiatanStats[] = (object) [
                'id' => $kegiatan->id,
                'nama_kegiatan' => $kegiatan->nama_kegiatan,
                'jam_pelaksanaan' => $kegiatan->jam_pelaksanaan,
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'total' => $total,
                'persentase' => $persentase,
            ];

            $totalHadirAll += $hadir;
            $totalAlpaAll += $alpa;
            $totalPertemuanAll += $total;
        }

        // Hitung statistik Puasa Sunnah
        $puasaStats = AbsensiPuasa::whereIn('santri_id', $santriIds)
            ->where('is_archived', false)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status_puasa = 'puasa' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status_puasa = 'izin' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN status_puasa IN ('sakit', 'haid') THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN status_puasa = 'tidak_puasa' THEN 1 ELSE 0 END) as alpa
            ")->first();

        $totalPuasa = (int) $puasaStats->total;
        if ($totalPuasa > 0) {
            $hadirPuasa = (int) $puasaStats->hadir;
            $izinPuasa = (int) $puasaStats->izin;
            $sakitPuasa = (int) $puasaStats->sakit;
            $alpaPuasa = (int) $puasaStats->alpa;
            $persentasePuasa = $totalPuasa > 0 ? round(($hadirPuasa / $totalPuasa) * 100, 1) : 0;
            
            $kegiatanStats[] = (object) [
                'id' => 'puasa',
                'nama_kegiatan' => 'Puasa Sunnah',
                'jam_pelaksanaan' => 'Senin & Kamis',
                'hadir' => $hadirPuasa,
                'izin' => $izinPuasa,
                'sakit' => $sakitPuasa,
                'alpa' => $alpaPuasa,
                'total' => $totalPuasa,
                'persentase' => $persentasePuasa,
            ];
            
            $totalHadirAll += $hadirPuasa;
            $totalAlpaAll += $alpaPuasa;
            $totalPertemuanAll += $totalPuasa;
        }

        $avgKehadiran = $totalPertemuanAll > 0 ? round(($totalHadirAll / $totalPertemuanAll) * 100, 1) : 0;

        return compact('kegiatanStats', 'daerahList', 'kamarsForSelectedDaerah', 'jenisKegiatanList', 'bulan', 'tahun', 'daerahId', 'kamarId', 'totalSantri', 'totalAlpaAll', 'avgKehadiran', 'showDaerahFilter');
    }

    /**
     * Halaman Rekapitulasi — Folder Kegiatan Grid
     */
    public function rekap(Request $request)
    {
        Gate::authorize('viewAny', AbsensiKegiatan::class);
        $data = $this->getRekapData($request);
        return view('absensi.rekap', $data);
    }

    /**
     * Tutup Buku — Arsipkan semua absensi pada bulan/tahun tertentu.
     */
    public function tutupBuku(Request $request)
    {
        $user = $request->user();
        if ($user->hasAnyRole(['dewan_pemangku', 'wali_santri'])) {
            abort(403, 'Aksi ini tidak diizinkan untuk role Anda.');
        }

        $request->merge([
            'bulan' => (int) $request->input('bulan'),
            'tahun' => (int) $request->input('tahun'),
        ]);

        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2099',
        ]);

        $bulan = (int) $request->input('bulan');
        $tahun = (int) $request->input('tahun');
        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);
        $scopeMode = $request->attributes->get('scopeMode', 'unit');

        // Ambil santri IDs sesuai scope user
        $santriIdsQuery = Santri::query();
        if ($scopeMode === 'santri') {
            $santriIdsQuery->where('id', $user->santri_id);
        } elseif ($scopeMode !== 'all') {
            if (!empty($allowedUnitIds)) {
                $santriIdsQuery->inUnitOrProgram($allowedUnitIds);
            } else {
                $santriIdsQuery->whereRaw('1 = 0');
            }
        }
        $santriIds = $santriIdsQuery->pluck('id');

        // Arsipkan Absensi Kegiatan
        $countKegiatan = AbsensiKegiatan::whereIn('santri_id', $santriIds)
            ->where('is_archived', false)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->update(['is_archived' => true]);

        // Arsipkan Absensi Puasa
        $countPuasa = AbsensiPuasa::whereIn('santri_id', $santriIds)
            ->where('is_archived', false)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->update(['is_archived' => true]);

        $namaBulan = date('F', mktime(0, 0, 0, $bulan, 10));

        return redirect()->route('absensi.rekap', ['bulan' => $bulan, 'tahun' => $tahun])
            ->with('success', "Buku bulan {$namaBulan} {$tahun} berhasil ditutup. {$countKegiatan} data kegiatan dan {$countPuasa} data puasa telah diarsipkan.");
    }

    /**
     * Buka Buku — Rollback arsip untuk bulan/tahun tertentu.
     */
    public function bukaBuku(Request $request)
    {
        $user = $request->user();
        if ($user->hasAnyRole(['dewan_pemangku', 'wali_santri'])) {
            abort(403, 'Aksi ini tidak diizinkan untuk role Anda.');
        }

        $request->merge([
            'bulan' => (int) $request->input('bulan'),
            'tahun' => (int) $request->input('tahun'),
        ]);

        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2099',
        ]);

        $bulan = (int) $request->input('bulan');
        $tahun = (int) $request->input('tahun');
        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);
        $scopeMode = $request->attributes->get('scopeMode', 'unit');

        $santriIdsQuery = Santri::query();
        if ($scopeMode === 'santri') {
            $santriIdsQuery->where('id', $user->santri_id);
        } elseif ($scopeMode !== 'all') {
            if (!empty($allowedUnitIds)) {
                $santriIdsQuery->inUnitOrProgram($allowedUnitIds);
            } else {
                $santriIdsQuery->whereRaw('1 = 0');
            }
        }
        $santriIds = $santriIdsQuery->pluck('id');

        AbsensiKegiatan::whereIn('santri_id', $santriIds)
            ->where('is_archived', true)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->update(['is_archived' => false]);

        AbsensiPuasa::whereIn('santri_id', $santriIds)
            ->where('is_archived', true)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->update(['is_archived' => false]);

        $namaBulan = date('F', mktime(0, 0, 0, $bulan, 10));

        return redirect()->route('arsip-absensi.index')
            ->with('success', "Arsip bulan {$namaBulan} {$tahun} berhasil dibuka kembali.");
    }

    /**
     * Export Excel — per kegiatan atau gabungan
     */
    public function exportExcel(Request $request)
    {
        Gate::authorize('viewAny', AbsensiKegiatan::class);

        $user = $request->user();
        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);
        $scopeMode = $request->attributes->get('scopeMode', 'unit');
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $daerahId = $request->get('daerah_id');
        $kegiatanId = $request->get('kegiatan_id');
        $kamarId = $request->get('kamar_id');

        if ($kegiatanId === 'puasa') {
            $santriQuery = Santri::with(['unitOrganisasi.parent', 'absensiPuasa' => function($q) use ($bulan, $tahun) {
                $q->where('is_archived', false)->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
            }]);
        } else {
            $santriQuery = Santri::with(['unitOrganisasi.parent', 'absensiKegiatan' => function($q) use ($bulan, $tahun, $kegiatanId) {
                $q->where('is_archived', false)->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
                if ($kegiatanId) {
                    $q->where('jenis_kegiatan_id', $kegiatanId);
                }
            }]);
        }

        if ($scopeMode === 'santri') {
            $santriQuery->where('id', $user->santri_id);
        } elseif (!empty($allowedUnitIds)) {
            $santriQuery->inUnitOrProgram($allowedUnitIds);
        }
        if ($daerahId && !$kamarId) {
            $childIds = UnitOrganisasi::where('parent_id', $daerahId)->pluck('id')->toArray();
            $santriQuery->whereIn('unit_organisasi_id', array_merge([$daerahId], $childIds));
        }
        if ($kamarId) {
            $santriQuery->where('unit_organisasi_id', $kamarId);
        }

        $santriList = $santriQuery->orderBy('nama')->get();

        $kegiatan = null;
        if ($kegiatanId === 'puasa') {
            $kegiatan = (object) ['id' => 'puasa', 'nama_kegiatan' => 'Puasa Sunnah'];
        } elseif ($kegiatanId) {
            $kegiatan = JenisKegiatan::find($kegiatanId);
        }

        $filename = "Rekap_Absensi_".date('F_Y', mktime(0, 0, 0, $bulan, 1, $tahun));
        if ($kegiatan) {
            $filename .= "_" . str_replace(' ', '_', $kegiatan->nama_kegiatan);
        }
        $filename .= ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($santriList, $kegiatanId, $kegiatan, $bulan, $tahun, $daerahId, $kamarId) {
            $file = fopen('php://output', 'w');
            
            // Tulis Header Informasi (Context)
            $bulanNama = date('F', mktime(0, 0, 0, $bulan, 10));
            fputcsv($file, ['LAPORAN REKAPITULASI ABSENSI SANTRI']);
            fputcsv($file, ['Bulan:', "$bulanNama $tahun"]);
            
            $daerahName = 'Semua Daerah';
            if ($daerahId) {
                $daerah = \App\Models\UnitOrganisasi::find($daerahId);
                $daerahName = $daerah ? $daerah->nama : 'Semua Daerah';
            }
            fputcsv($file, ['Daerah:', $daerahName]);
            
            $kamarName = 'Semua Kamar';
            if ($kamarId) {
                $kamar = \App\Models\UnitOrganisasi::find($kamarId);
                $kamarName = $kamar ? $kamar->nama : 'Semua Kamar';
            }
            fputcsv($file, ['Kamar:', $kamarName]);
            
            if ($kegiatan) {
                fputcsv($file, ['Kegiatan:', $kegiatan->nama_kegiatan]);
            }
            fputcsv($file, []); // Baris kosong pemisah
            
            // Tulis Header Tabel
            fputcsv($file, ['No', 'Nama Santri', 'NIS', 'Daerah', 'Kamar', 'Hadir (H)', 'Izin (I)', 'Sakit (S)', 'Alpa (A)', 'Total', 'Persentase (%)']);
            foreach ($santriList as $index => $santri) {
                if ($kegiatanId === 'puasa') {
                    $hadir = $santri->absensiPuasa->where('status_puasa', 'puasa')->count();
                    $izin = $santri->absensiPuasa->where('status_puasa', 'izin')->count();
                    $sakit = $santri->absensiPuasa->whereIn('status_puasa', ['sakit', 'haid'])->count();
                    $alpa = $santri->absensiPuasa->where('status_puasa', 'tidak_puasa')->count();
                } else {
                    $hadir = $santri->absensiKegiatan->where('status', 'hadir')->count();
                    $izin = $santri->absensiKegiatan->where('status', 'izin')->count();
                    $sakit = $santri->absensiKegiatan->where('status', 'sakit')->count();
                    $alpa = $santri->absensiKegiatan->where('status', 'alpa')->count();
                }
                $total = $hadir + $izin + $sakit + $alpa;
                $persen = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;
                fputcsv($file, [
                    $index + 1, $santri->nama, $santri->nis_induk,
                    $santri->unitOrganisasi->parent->nama ?? '-',
                    $santri->unitOrganisasi->nama ?? '-',
                    $hadir, $izin, $sakit, $alpa, $total, $persen
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export PDF — per kegiatan atau gabungan
     */
    public function exportPdf(Request $request)
    {
        Gate::authorize('viewAny', AbsensiKegiatan::class);

        $user = $request->user();
        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);
        $scopeMode = $request->attributes->get('scopeMode', 'unit');
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $daerahId = $request->get('daerah_id');
        $kegiatanId = $request->get('kegiatan_id');
        $kamarId = $request->get('kamar_id');

        if ($kegiatanId === 'puasa') {
            $santriQuery = Santri::with(['unitOrganisasi.parent', 'absensiPuasa' => function($q) use ($bulan, $tahun) {
                $q->where('is_archived', false)->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
            }]);
        } else {
            $santriQuery = Santri::with(['unitOrganisasi.parent', 'absensiKegiatan' => function($q) use ($bulan, $tahun, $kegiatanId) {
                $q->where('is_archived', false)->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
                if ($kegiatanId) { $q->where('jenis_kegiatan_id', $kegiatanId); }
            }]);
        }

        if ($scopeMode === 'santri') { $santriQuery->where('id', $user->santri_id); }
        elseif (!empty($allowedUnitIds)) { $santriQuery->inUnitOrProgram($allowedUnitIds); }
        if ($daerahId && !$kamarId) {
            $childIds = UnitOrganisasi::where('parent_id', $daerahId)->pluck('id')->toArray();
            $santriQuery->whereIn('unit_organisasi_id', array_merge([$daerahId], $childIds));
        }
        if ($kamarId) {
            $santriQuery->where('unit_organisasi_id', $kamarId);
        }

        $daerahList = UnitOrganisasi::where('tipe', 'daerah')
            ->when($scopeMode !== 'all', fn($q) => $q->whereIn('id', $allowedUnitIds))
            ->get();

        $santriList = $santriQuery->orderBy('nama')->get();
        $totalSantri = $santriList->count();
        $totalHadirAll = 0;
        $totalPertemuanAll = 0;

        foreach ($santriList as $santri) {
            if ($kegiatanId === 'puasa') {
                $hadir = $santri->absensiPuasa->where('status_puasa', 'puasa')->count();
                $izin = $santri->absensiPuasa->where('status_puasa', 'izin')->count();
                $sakit = $santri->absensiPuasa->whereIn('status_puasa', ['sakit', 'haid'])->count();
                $alpa = $santri->absensiPuasa->where('status_puasa', 'tidak_puasa')->count();
            } else {
                $hadir = $santri->absensiKegiatan->where('status', 'hadir')->count();
                $izin = $santri->absensiKegiatan->where('status', 'izin')->count();
                $sakit = $santri->absensiKegiatan->where('status', 'sakit')->count();
                $alpa = $santri->absensiKegiatan->where('status', 'alpa')->count();
            }
            $total = $hadir + $izin + $sakit + $alpa;
            $santri->rekap = (object) ['hadir' => $hadir, 'izin' => $izin, 'sakit' => $sakit, 'alpa' => $alpa, 'total_pertemuan' => $total, 'persentase' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0];
            
            $totalHadirAll += $hadir;
            $totalPertemuanAll += $total;
        }

        $avgKehadiran = $totalPertemuanAll > 0 ? round(($totalHadirAll / $totalPertemuanAll) * 100, 1) : 0;

        if (!class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            return back()->with('error', 'Package barryvdh/laravel-dompdf belum terinstall.');
        }

        $kegiatan = null;
        if ($kegiatanId === 'puasa') {
            $kegiatan = (object) ['id' => 'puasa', 'nama_kegiatan' => 'Puasa Sunnah'];
        } elseif ($kegiatanId) {
            $kegiatan = JenisKegiatan::find($kegiatanId);
        }

        $data = compact('santriList', 'bulan', 'tahun', 'daerahId', 'daerahList', 'totalSantri', 'avgKehadiran', 'kegiatan');
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('absensi.pdf_rekap', $data)->setPaper('a4', 'landscape');
        
        $filename = "Rekap_Absensi_".date('F_Y', mktime(0, 0, 0, $bulan, 1, $tahun));
        if ($kegiatan) {
            $filename .= "_" . str_replace(' ', '_', $kegiatan->nama_kegiatan);
        }
        $filename .= ".pdf";
        return $pdf->stream($filename);
    }

    /**
     * Endpoint API JSON untuk Modal Drill-down Folder Kegiatan
     * Mengembalikan data per-santri (H/I/S/A) untuk satu kegiatan spesifik
     */
    public function detailRekap(Request $request)
    {
        Gate::authorize('viewAny', AbsensiKegiatan::class);

        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');
        $kegiatanId = $request->get('kegiatan_id');
        $daerahId = $request->get('daerah_id');
        $kamarId = $request->get('kamar_id');

        if (!$bulan || !$tahun || !$kegiatanId) {
            return response()->json(['error' => 'Parameter tidak lengkap'], 400);
        }

        $user = $request->user();
        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);
        $scopeMode = $request->attributes->get('scopeMode', 'unit');
        $showDaerahFilter = $user->hasAnyRole(['admin', 'pengurus_wilayah', 'dewan_pemangku']);

        $daerahList = \App\Models\UnitOrganisasi::whereIn('tipe', ['daerah', 'daerah_pendidikan'])
            ->when($scopeMode !== 'all', fn($q) => $q->whereIn('id', $allowedUnitIds))
            ->orderBy('nama')
            ->get();

        $allowedDaerahIds = $daerahList->pluck('id')->toArray();
        
        $kamarsForSelectedDaerah = \App\Models\UnitOrganisasi::whereIn('tipe', ['kamar', 'sektor'])
            ->when($daerahId, fn($q) => $q->where('parent_id', $daerahId))
            ->when($scopeMode !== 'all', fn($q) => $q->whereIn('parent_id', $allowedDaerahIds))
            ->orderBy('nama')
            ->get();

        if ($kegiatanId === 'puasa') {
            $santriQuery = Santri::with(['unitOrganisasi.parent', 'absensiPuasa' => function($q) use ($bulan, $tahun) {
                $q->where('is_archived', false)
                  ->whereMonth('tanggal', $bulan)
                  ->whereYear('tanggal', $tahun);
            }]);
        } else {
            $santriQuery = Santri::with(['unitOrganisasi.parent', 'absensiKegiatan' => function($q) use ($bulan, $tahun, $kegiatanId) {
                $q->where('jenis_kegiatan_id', $kegiatanId)
                  ->where('is_archived', false)
                  ->whereMonth('tanggal', $bulan)
                  ->whereYear('tanggal', $tahun);
            }]);
        }

        if ($scopeMode === 'santri') {
            $santriQuery->where('id', $user->santri_id);
        } elseif (!empty($allowedUnitIds)) {
            $santriQuery->inUnitOrProgram($allowedUnitIds);
        }

        if ($daerahId && !$kamarId) {
            $childIds = UnitOrganisasi::where('parent_id', $daerahId)->pluck('id')->toArray();
            $santriQuery->whereIn('unit_organisasi_id', array_merge([$daerahId], $childIds));
        }
        if ($kamarId) {
            $santriQuery->where('unit_organisasi_id', $kamarId);
        }

        $santriList = $santriQuery->orderBy('nama')->get();

        $result = $santriList->map(function ($santri) use ($kegiatanId) {
            if ($kegiatanId === 'puasa') {
                $hadir = $santri->absensiPuasa->where('status_puasa', 'puasa')->count();
                $izin = $santri->absensiPuasa->where('status_puasa', 'izin')->count();
                $sakit = $santri->absensiPuasa->whereIn('status_puasa', ['sakit', 'haid'])->count();
                $alpa = $santri->absensiPuasa->where('status_puasa', 'tidak_puasa')->count();
            } else {
                $hadir = $santri->absensiKegiatan->where('status', 'hadir')->count();
                $izin = $santri->absensiKegiatan->where('status', 'izin')->count();
                $sakit = $santri->absensiKegiatan->where('status', 'sakit')->count();
                $alpa = $santri->absensiKegiatan->where('status', 'alpa')->count();
            }
            $total = $hadir + $izin + $sakit + $alpa;

            return [
                'id' => $santri->id,
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
        });

        if ($kegiatanId === 'puasa') {
            $kegiatan = (object) ['id' => 'puasa', 'nama_kegiatan' => 'Puasa Sunnah'];
        } else {
            $kegiatan = JenisKegiatan::findOrFail($kegiatanId);
        }
        $detailData = $result;

        return view('absensi.rekap-detail', compact('detailData', 'kegiatan', 'bulan', 'tahun', 'daerahId', 'kamarId', 'daerahList', 'kamarsForSelectedDaerah', 'showDaerahFilter'));
    }

    /**
     * Endpoint API JSON untuk Drill-down Riwayat Absensi Per Santri
     * Mengembalikan daftar tanggal beserta status kehadiran santri pada kegiatan tertentu
     */
    public function detailRiwayatSantri(Request $request)
    {
        Gate::authorize('viewAny', AbsensiKegiatan::class);

        $santriId = $request->get('santri_id');
        $kegiatanId = $request->get('kegiatan_id');
        $bulan = (int) $request->get('bulan');
        $tahun = (int) $request->get('tahun');

        if (!$santriId || !$kegiatanId || !$bulan || !$tahun) {
            return response()->json(['error' => 'Parameter tidak lengkap'], 400);
        }

        $santri = Santri::with('unitOrganisasi.parent')->findOrFail($santriId);

        // Build date range for the month
        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        if ($kegiatanId === 'puasa') {
            $riwayat = AbsensiPuasa::where('santri_id', $santriId)
                ->where('is_archived', false)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->orderBy('tanggal', 'asc')
                ->get()
                ->map(function ($item) {
                    $tgl = $item->tanggal;
                    $namaHari = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
                    $namaBulan = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];

                    $status_label = match($item->status_puasa) {
                        'puasa' => 'Puasa',
                        'tidak_puasa' => 'Tidak Puasa',
                        'haid' => 'Haid',
                        'sakit' => 'Sakit',
                        'izin' => 'Izin',
                        default => ucfirst($item->status_puasa)
                    };

                    return [
                        'tanggal' => $tgl->format('Y-m-d'),
                        'tanggal_label' => $tgl->format('d') . ' ' . ($namaBulan[$tgl->format('F')] ?? $tgl->format('F')) . ' ' . $tgl->format('Y'),
                        'hari' => $namaHari[$tgl->format('l')] ?? $tgl->format('l'),
                        'status' => $item->status_puasa,
                        'status_label' => $status_label,
                        'keterangan' => $item->keterangan,
                    ];
                });
        } else {
            $riwayat = AbsensiKegiatan::where('santri_id', $santriId)
                ->where('jenis_kegiatan_id', $kegiatanId)
                ->where('is_archived', false)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->orderBy('tanggal', 'asc')
                ->get()
                ->map(function ($item) {
                    $tgl = $item->tanggal;
                    $namaHari = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
                    $namaBulan = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];

                    return [
                        'tanggal' => $tgl->format('Y-m-d'),
                        'tanggal_label' => $tgl->format('d') . ' ' . ($namaBulan[$tgl->format('F')] ?? $tgl->format('F')) . ' ' . $tgl->format('Y'),
                        'hari' => $namaHari[$tgl->format('l')] ?? $tgl->format('l'),
                        'status' => $item->status,
                        'status_label' => $item->status_label,
                        'keterangan' => $item->keterangan,
                    ];
                });
        }

        return response()->json([
            'santri' => [
                'id' => $santri->id,
                'nama' => $santri->nama,
                'nis' => $santri->nis_induk,
                'daerah' => $santri->unitOrganisasi->parent->nama ?? '-',
                'kamar' => $santri->unitOrganisasi->nama ?? '-',
            ],
            'riwayat' => $riwayat,
        ]);
    }

    /**
     * Halaman Rekap Harian khusus Wali Santri (Read Only)
     */
    public function rekapHarianWali(Request $request)
    {
        $user = $request->user();
        // Cek ID santri terhubung (akses_wali_santri_id atau santri_id)
        $santriId = $user->akses_wali_santri_id ?? $user->santri_id;

        // Jika belum terhubung, otomatis hubungkan ke santri pertama yang ada di database
        if (!$santriId) {
            $firstSantri = Santri::first();
            if ($firstSantri) {
                $user->update([
                    'santri_id'            => $firstSantri->id,
                    'akses_wali_santri_id' => $firstSantri->id,
                ]);
                $santriId = $firstSantri->id;
            } else {
                abort(404, 'Belum ada data santri yang terdaftar di dalam sistem.');
            }
        }

        $santri = Santri::findOrFail($santriId);
        $tanggalHariIni = Carbon::today()->format('Y-m-d');
        $isSeninKamis = Carbon::today()->isMonday() || Carbon::today()->isThursday();

        // Cari Unit Daerah dari santri
        $daerah = $santri->unitOrganisasi;
        if ($daerah && $daerah->tipe === 'kamar' && $daerah->parent_id) {
            $daerah = $daerah->parent;
        }
        $isPuasaWajib = $daerah ? (bool) $daerah->is_puasa_wajib : false;

        // Ambil semua jenis kegiatan yang relevan untuk santri ini (termasuk kegiatan Daerah dan Wilayah)
        $jenisKegiatanList = JenisKegiatan::where(function ($query) use ($santri) {
            $query->where('unit_organisasi_id', $santri->unit_organisasi_id);
            if ($santri->unitOrganisasi && $santri->unitOrganisasi->parent_id) {
                $query->orWhere('unit_organisasi_id', $santri->unitOrganisasi->parent_id);
            }
            $query->orWhereNull('unit_organisasi_id');
        })->where('aktif', true)->orderBy('nama_kegiatan', 'asc')->get();

        // Ambil semua data absensi kegiatan hari ini untuk santri tersebut
        $absensiKegiatans = AbsensiKegiatan::where('santri_id', $santriId)
            ->where('tanggal', $tanggalHariIni)
            ->get()
            ->keyBy('jenis_kegiatan_id');

        // Ambil data puasa hari ini (jika Senin/Kamis)
        $absensiPuasa = null;
        if ($isSeninKamis) {
            $absensiPuasa = AbsensiPuasa::where('santri_id', $santriId)
                ->where('tanggal', $tanggalHariIni)
                ->first();
        }

        return view('wali-santri.absensi.hari-ini', compact(
            'santri',
            'tanggalHariIni',
            'jenisKegiatanList',
            'absensiKegiatans',
            'absensiPuasa',
            'isSeninKamis',
            'isPuasaWajib'
        ));
    }

    /**
     * Mengaktifkan atau menonaktifkan aturan Wajib Puasa Sunnah untuk Daerah.
     */
    public function togglePuasaWajib(Request $request, $id)
    {
        // Hanya yang berwenang (misal pengurus wilayah/daerah/admin)
        if (!$request->user()->hasAnyRole(['admin', 'pengurus_wilayah', 'pengurus_daerah'])) {
            abort(403, 'Aksi ini tidak diizinkan.');
        }

        $unit = UnitOrganisasi::findOrFail($id);
        $unit->is_puasa_wajib = !$unit->is_puasa_wajib;
        $unit->save();

        return back()->with('success', 'Status Wajib Puasa untuk ' . $unit->nama . ' berhasil di' . ($unit->is_puasa_wajib ? 'aktifkan' : 'nonaktifkan') . '.');
    }
}
