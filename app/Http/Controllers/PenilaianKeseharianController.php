<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenilaianKeseharianRequest;
use App\Models\AspekPenilaian;
use App\Models\PenilaianKeseharian;
use App\Models\Santri;
use App\Models\UnitOrganisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PenilaianKeseharianController extends Controller
{
    /**
     * Tampilkan daftar penilaian keseharian santri dengan filter.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', PenilaianKeseharian::class);

        $user = $request->user();
        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);
        $scopeMode = $request->attributes->get('scopeMode', 'unit');

        // Dropdown Daerah — scope-aware
        $daerahList = UnitOrganisasi::where('tipe', 'daerah')
            ->when($scopeMode !== 'all', fn($q) => $q->whereIn('id', $allowedUnitIds))
            ->orderBy('nama')
            ->get();

        $daerahId = $request->get('daerah_id');
        $kamarsForSelectedDaerah = UnitOrganisasi::where('tipe', 'sektor')
            ->when($daerahId, fn($q) => $q->where('parent_id', $daerahId))
            ->when($scopeMode !== 'all', fn($q) => $q->whereIn('id', $allowedUnitIds))
            ->orderBy('nama')
            ->get();

        $query = PenilaianKeseharian::with(['santri.unitOrganisasi', 'waliAsuh']);

        // Scope data — unified
        if ($scopeMode === 'santri') {
            $query->where('santri_id', $user->santri_id);
        } elseif (!empty($allowedUnitIds)) {
            $query->whereHas('santri', function ($q) use ($allowedUnitIds) {
                $q->whereIn('unit_organisasi_id', $allowedUnitIds);
            });
        }

        // Filter Periode (YYYY-MM)
        if ($request->filled('periode')) {
            $query->where('periode', $request->periode);
        }

        // Filter Kategori Evaluasi
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Filter Search (nama/NIS santri)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('santri', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis_induk', 'like', "%{$search}%");
            });
        }

        $penilaian = $query->orderBy('periode', 'desc')
                           ->orderBy('tanggal_penilaian', 'desc')
                           ->paginate(10);

        return view('penilaian-keseharian.index', compact('penilaian', 'daerahList', 'kamarsForSelectedDaerah'));
    }

    /**
     * Form input penilaian keseharian santri.
     */
    public function create(Request $request)
    {
        Gate::authorize('create', PenilaianKeseharian::class);

        $user = $request->user();
        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);

        $santriQuery = Santri::query();
        if (!empty($allowedUnitIds)) {
            $santriQuery->whereIn('unit_organisasi_id', $allowedUnitIds);
        }

        $santriList = $santriQuery->orderBy('nama')->get();
        
        $aspekQuery = AspekPenilaian::query();
        if ($user->hasRole('pengurus_wilayah') || (!$user->hasAnyRole(['admin', 'dewan_pemangku']))) {
            $aspekQuery->where(function ($q) use ($allowedUnitIds) {
                $q->whereIn('unit_organisasi_id', $allowedUnitIds)
                  ->orWhereNull('unit_organisasi_id');
            });
        }
        $aspekList = $aspekQuery->orderBy('nomor_urut')->get();
        
        $selectedSantriId = $request->get('santri_id');

        return view('penilaian-keseharian.create', compact('santriList', 'aspekList', 'selectedSantriId'));
    }

    /**
     * Simpan penilaian keseharian santri beserta detail 15 aspek.
     */
    public function store(StorePenilaianKeseharianRequest $request)
    {
        $validated = $request->validated();
        $waliAsuhId = auth()->id();

        $jumlahSkor = array_sum($validated['skor']);
        $nilaiAkhir = round(($jumlahSkor / 60) * 100, 2);
        $kategori = PenilaianKeseharian::tentukanKategori($nilaiAkhir);

        DB::transaction(function () use ($validated, $waliAsuhId, $jumlahSkor, $nilaiAkhir, $kategori) {
            $penilaian = PenilaianKeseharian::create([
                'santri_id'              => $validated['santri_id'],
                'wali_asuh_id'           => $waliAsuhId,
                'periode'                => $validated['periode'],
                'tanggal_penilaian'      => $validated['tanggal_penilaian'],
                'jumlah_skor'            => $jumlahSkor,
                'nilai_akhir'            => $nilaiAkhir,
                'kategori'               => $kategori,
                'catatan_kelebihan'      => $validated['catatan_kelebihan'],
                'catatan_peningkatan'    => $validated['catatan_peningkatan'],
                'rencana_tindak_lanjut'  => $validated['rencana_tindak_lanjut'],
            ]);

            foreach ($validated['skor'] as $aspekId => $skor) {
                $penilaian->detail()->create([
                    'aspek_id' => $aspekId,
                    'skor'     => $skor,
                ]);
            }
        });

        return redirect()->route('penilaian-keseharian.index')
            ->with('success', 'Penilaian keseharian santri berhasil disimpan.');
    }

    /**
     * Tampilkan detail Rapor Keseharian Santri.
     */
    public function show(PenilaianKeseharian $penilaianKeseharian)
    {
        Gate::authorize('view', $penilaianKeseharian);

        $penilaianKeseharian->load([
            'santri.unitOrganisasi.parent',
            'waliAsuh',
            'detail.aspek',
        ]);

        return view('penilaian-keseharian.show', ['penilaian' => $penilaianKeseharian]);
    }

    /**
     * Export Rapor Keseharian ke PDF.
     */
    public function exportPdf(PenilaianKeseharian $penilaianKeseharian)
    {
        Gate::authorize('view', $penilaianKeseharian);

        $penilaianKeseharian->load([
            'santri.unitOrganisasi.parent',
            'waliAsuh',
            'detail.aspek',
        ]);

        if (!class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            return back()->with('error', 'Package barryvdh/laravel-dompdf belum terinstall.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('penilaian-keseharian.pdf_rapor', ['penilaian' => $penilaianKeseharian])
            ->setPaper('a4', 'portrait');

        $filename = "Rapor_Keseharian_" . str_replace(' ', '_', $penilaianKeseharian->santri->nama) . "_" . $penilaianKeseharian->periode . ".pdf";
        return $pdf->stream($filename);
    }
    /**
     * Simpan catatan evaluasi dari Dewan Pemangku.
     */
    public function storeCatatanPemangku(Request $request, PenilaianKeseharian $penilaianKeseharian)
    {
        Gate::authorize('update', $penilaianKeseharian); // Or specific permission check if needed. Since role is dewan_pemangku, we will check role.
        
        if (!auth()->user()->hasRole('dewan_pemangku')) {
            abort(403, 'Hanya Dewan Pemangku yang dapat menambahkan catatan.');
        }

        $validated = $request->validate([
            'catatan_pemangku' => 'nullable|string',
        ]);

        $penilaianKeseharian->update([
            'catatan_pemangku' => $validated['catatan_pemangku'],
        ]);

        return redirect()->back()->with('success', 'Catatan Dewan Pemangku berhasil disimpan.');
    }
}
