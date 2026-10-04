<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePelanggaranRequest;
use App\Models\Pelanggaran;
use App\Models\Santri;
use App\Models\UnitOrganisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PelanggaranController extends Controller
{
    /**
     * Tampilkan daftar pelanggaran dengan filter.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Pelanggaran::class);

        $user = $request->user();
        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);
        $scopeMode = $request->attributes->get('scopeMode', 'unit');

        // Dropdown Daerah — scope-aware
        $daerahList = UnitOrganisasi::where('tipe', 'daerah')
            ->when($scopeMode !== 'all', fn($q) => $q->whereIn('id', $allowedUnitIds))
            ->orderBy('nama')
            ->get();

        $daerahId = $request->get('daerah_id');
        $allowedDaerahIds = $daerahList->pluck('id')->toArray();
        
        $kamarsForSelectedDaerah = UnitOrganisasi::where('tipe', 'sektor')
            ->when($daerahId, fn($q) => $q->where('parent_id', $daerahId))
            ->when($scopeMode !== 'all', fn($q) => $q->whereIn('parent_id', $allowedDaerahIds))
            ->orderBy('nama')
            ->get();

        $query = Pelanggaran::with(['santri.unitOrganisasi', 'pencatat', 'pembinaan']);

        // Scope data pelanggaran — unified
        if ($scopeMode === 'santri') {
            $query->where('santri_id', $user->santri_id);
        } elseif ($scopeMode !== 'all') {
            $query->whereHas('santri', function ($q) use ($allowedUnitIds) {
                $q->whereIn('unit_organisasi_id', $allowedUnitIds);
            });
        }

        // Filter Kategori (umum/devisi)
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Filter Tingkat (sektor/daerah)
        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->tingkat);
        }

        // Filter Search (nama/NIS santri)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('santri', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis_induk', 'like', "%{$search}%");
            });
        }

        $pelanggaran = $query->orderBy('tanggal', 'desc')->paginate(10);

        return view('pelanggaran.index', compact('pelanggaran', 'daerahList', 'kamarsForSelectedDaerah'));
    }

    /**
     * Form catat pelanggaran baru.
     */
    public function create(Request $request)
    {
        Gate::authorize('create', Pelanggaran::class);

        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);
        $scopeMode = $request->attributes->get('scopeMode', 'unit');

        $santriQuery = Santri::query();
        if (!empty($allowedUnitIds)) {
            $santriQuery->whereIn('unit_organisasi_id', $allowedUnitIds);
        }

        $santriList = $santriQuery->orderBy('nama')->get();

        return view('pelanggaran.create', compact('santriList'));
    }

    /**
     * Simpan pelanggaran baru.
     */
    public function store(StorePelanggaranRequest $request)
    {
        $validated = $request->validated();
        $validated['pencatat_id'] = auth()->id();

        Pelanggaran::create($validated);

        return redirect()->route('pelanggaran.index')
            ->with('success', 'Data pelanggaran berhasil dicatat.');
    }

    /**
     * Detail pelanggaran beserta riwayat pembinaan.
     */
    public function show(Pelanggaran $pelanggaran)
    {
        Gate::authorize('view', $pelanggaran);

        $pelanggaran->load(['santri.unitOrganisasi', 'pencatat', 'pembinaan.pembina']);

        return view('pelanggaran.show', compact('pelanggaran'));
    }
}
