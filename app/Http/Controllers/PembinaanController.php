<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembinaan;
use App\Models\Pelanggaran;
use App\Http\Requests\StorePembinaanRequest;
use Illuminate\Support\Facades\Gate;

class PembinaanController extends Controller
{
    /**
     * Riwayat pembinaan per santri.
     */
    public function index(Request $request)
    {
        $query = Pembinaan::with(['pelanggaran.santri', 'pembina']);

        if ($request->filled('santri_id')) {
            $query->whereHas('pelanggaran', function ($q) use ($request) {
                $q->where('santri_id', $request->santri_id);
            });
        }

        $pembinaan = $query->latest('tanggal')->paginate(15);

        return view('pembinaan.index', compact('pembinaan'));
    }

    /**
     * Simpan pembinaan baru (dipanggil dari halaman detail pelanggaran).
     */
    public function store(StorePembinaanRequest $request)
    {
        $pelanggaran = Pelanggaran::findOrFail($request->pelanggaran_id);

        Gate::authorize('addPembinaan', $pelanggaran);

        $data = $request->validated();
        $data['pembina_id'] = auth()->id();

        Pembinaan::create($data);

        return redirect()->route('pelanggaran.show', $pelanggaran)
            ->with('success', 'Data pembinaan berhasil ditambahkan.');
    }
}
