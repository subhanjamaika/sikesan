<?php

namespace App\Http\Controllers;

use App\Models\AspekPenilaian;
use App\Models\UnitOrganisasi;
use Illuminate\Http\Request;

class AspekPenilaianController extends Controller
{
    /**
     * Tampilkan daftar Aspek Penilaian berdasarkan Daerah (unit_organisasi_id)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);
        $daerahs = collect();
        
        $query = AspekPenilaian::with('unitOrganisasi');

        $filterDaerahId = $request->get('daerah_id');
        if ($filterDaerahId) {
            $query->where('unit_organisasi_id', $filterDaerahId);
        }

        // 1. Admin / Dewan Pemangku -> Semua Data
        if ($user->hasAnyRole(['admin', 'dewan_pemangku'])) {
            $daerahs = UnitOrganisasi::where('tipe', 'daerah')->orderBy('nama')->get();
        }
        // 2. Pengurus Wilayah -> Data di bawah wilayahnya + Global
        elseif ($user->hasRole('pengurus_wilayah')) {
            if (!$filterDaerahId) {
                $query->where(function ($q) use ($allowedUnitIds) {
                    $q->whereIn('unit_organisasi_id', $allowedUnitIds)
                      ->orWhereNull('unit_organisasi_id');
                });
            }
            $daerahs = UnitOrganisasi::where('tipe', 'daerah')
                          ->whereIn('id', $allowedUnitIds)->orderBy('nama')->get();
        }
        // 3. Daerah / Sektor -> Hanya unit mereka + Global
        else {
            $query->where(function ($q) use ($allowedUnitIds) {
                $q->whereIn('unit_organisasi_id', $allowedUnitIds)
                  ->orWhereNull('unit_organisasi_id');
            });
        }

        $aspekList = $query->orderBy('nomor_urut')->get();

        return view('aspek-penilaian.index', compact('aspekList', 'daerahs'));
    }

    /**
     * Simpan Aspek Penilaian Baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_aspek'         => 'required|string|max:255',
            'indikator'          => 'required|string',
            'nomor_urut'         => 'nullable|integer',
            'unit_organisasi_id' => 'nullable|exists:unit_organisasi,id',
        ]);

        if (empty($validated['nomor_urut'])) {
            $maxUrutan = AspekPenilaian::max('nomor_urut') ?? 0;
            $validated['nomor_urut'] = $maxUrutan + 1;
        }

        $user = $request->user();
        if (!$user->hasAnyRole(['admin', 'pengurus_wilayah'])) {
            // Paksa ke daerah milik user jika bukan Admin/Wilayah
            $validated['unit_organisasi_id'] = $user->unit_organisasi_id;
        }

        $aspek = AspekPenilaian::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Aspek Penilaian berhasil ditambahkan.',
                'data'    => $aspek,
            ]);
        }

        return redirect()->back()->with('success', 'Aspek Penilaian "' . $aspek->nama_aspek . '" berhasil ditambahkan.');
    }

    /**
     * Perbarui Aspek Penilaian
     */
    public function update(Request $request, string $id)
    {
        $aspek = AspekPenilaian::findOrFail($id);
        $user = $request->user();
        
        // Cek izin (jika bukan admin/wilayah, hanya boleh ubah aspek daerah sendiri)
        if (!$user->hasAnyRole(['admin', 'pengurus_wilayah'])) {
            if ($aspek->unit_organisasi_id !== null && $aspek->unit_organisasi_id !== $user->unit_organisasi_id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $validated = $request->validate([
            'nama_aspek'         => 'required|string|max:255',
            'indikator'          => 'required|string',
            'nomor_urut'         => 'nullable|integer',
            'unit_organisasi_id' => 'nullable|exists:unit_organisasi,id',
        ]);

        if (!$user->hasAnyRole(['admin', 'pengurus_wilayah'])) {
            // Hindari user Daerah mengubah lingkup ke Global atau Daerah lain
            unset($validated['unit_organisasi_id']);
        }

        $aspek->update($validated);

        return redirect()->back()->with('success', 'Aspek Penilaian "' . $aspek->nama_aspek . '" berhasil diperbarui.');
    }

    /**
     * Hapus Aspek Penilaian
     */
    public function destroy(Request $request, string $id)
    {
        $aspek = AspekPenilaian::findOrFail($id);
        $user = $request->user();
        
        if (!$user->hasAnyRole(['admin', 'pengurus_wilayah'])) {
            if ($aspek->unit_organisasi_id !== null && $aspek->unit_organisasi_id !== $user->unit_organisasi_id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $penilaianCount = $aspek->penilaianDetail()->count();

        if ($penilaianCount > 0) {
            return redirect()->back()->with('error', 'Aspek Penilaian "' . $aspek->nama_aspek . '" tidak dapat dihapus karena sudah memiliki ' . $penilaianCount . ' data nilai santri.');
        }

        $nama = $aspek->nama_aspek;
        $aspek->delete();

        return redirect()->back()->with('success', 'Aspek Penilaian "' . $nama . '" berhasil dihapus.');
    }
}
