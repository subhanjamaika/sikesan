<?php

namespace App\Http\Controllers;

use App\Models\JenisKegiatan;
use App\Enums\ProgramEnum;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KegiatanDaerahController extends Controller
{
    /**
     * Tampilkan daftar Kegiatan berdasarkan Daerah (unit_organisasi_id)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);
        $daerahs = collect();
        
        $query = JenisKegiatan::withCount('absensiKegiatan')->with('unitOrganisasi');

        $filterDaerahId = $request->get('daerah_id');
        if ($filterDaerahId) {
            $query->where('unit_organisasi_id', $filterDaerahId);
        }

        // 1. Admin / Dewan Pemangku -> Semua Data
        if ($user->hasAnyRole(['admin', 'dewan_pemangku'])) {
            $daerahs = \App\Models\UnitOrganisasi::where('tipe', 'daerah')->get();
        }
        // 2. Pengurus Wilayah -> Data di bawah wilayahnya + Global
        elseif ($user->hasRole('pengurus_wilayah')) {
            if (!$filterDaerahId) {
                $query->where(function ($q) use ($allowedUnitIds) {
                    $q->whereIn('unit_organisasi_id', $allowedUnitIds)
                      ->orWhereNull('unit_organisasi_id');
                });
            }
            $daerahs = \App\Models\UnitOrganisasi::where('tipe', 'daerah')
                          ->whereIn('id', $allowedUnitIds)->get();
        }
        // 3. Daerah / Sektor -> Hanya unit mereka + Global
        else {
            $query->where(function ($q) use ($allowedUnitIds) {
                $q->whereIn('unit_organisasi_id', $allowedUnitIds)
                  ->orWhereNull('unit_organisasi_id');
            });
        }

        $kegiatanList = $query->orderBy('nama_kegiatan', 'asc')->get();
        $programEnums = ProgramEnum::cases();
        
        // Buat map unit_organisasi_id -> array of program value
        $programMap = [];
        $allDaerahs = \App\Models\UnitOrganisasi::where('tipe', 'daerah')->get();
        foreach ($allDaerahs as $d) {
            $programMap[$d->id] = \App\Enums\ProgramEnum::getProgramIdsForDaerah($d->nama);
        }

        return view('kegiatan.index', compact('kegiatanList', 'daerahs', 'programEnums', 'programMap'));
    }

    /**
     * Simpan Kegiatan Baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kegiatan'      => 'required|string|max:255',
            'jam_pelaksanaan'    => 'nullable|date_format:H:i',
            'aktif'              => 'nullable|boolean',
            'unit_organisasi_id' => 'nullable|exists:unit_organisasi,id',
            'program_id'         => ['nullable', Rule::enum(ProgramEnum::class)],
        ]);

        $validated['aktif'] = $request->has('aktif') ? (bool) $request->aktif : true;

        $user = $request->user();
        if (!$user->hasAnyRole(['admin', 'pengurus_wilayah'])) {
            // Paksa ke daerah milik user jika bukan Admin/Wilayah
            $validated['unit_organisasi_id'] = $user->unit_organisasi_id;
        }

        $kegiatan = JenisKegiatan::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kegiatan berhasil ditambahkan.',
                'data'    => $kegiatan,
            ]);
        }

        return redirect()->back()->with('success', 'Kegiatan "' . $kegiatan->nama_kegiatan . '" berhasil ditambahkan.');
    }

    /**
     * Perbarui Kegiatan
     */
    public function update(Request $request, string $id)
    {
        $kegiatan = JenisKegiatan::findOrFail($id);
        $user = $request->user();
        
        // Cek izin (jika bukan admin/wilayah, hanya boleh ubah kegiatan daerah sendiri)
        if (!$user->hasAnyRole(['admin', 'pengurus_wilayah'])) {
            if ($kegiatan->unit_organisasi_id !== null && $kegiatan->unit_organisasi_id !== $user->unit_organisasi_id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $validated = $request->validate([
            'nama_kegiatan'      => 'required|string|max:255',
            'jam_pelaksanaan'    => 'nullable|date_format:H:i',
            'aktif'              => 'nullable|boolean',
            'unit_organisasi_id' => 'nullable|exists:unit_organisasi,id',
            'program_id'         => ['nullable', Rule::enum(ProgramEnum::class)],
        ]);

        $validated['aktif'] = $request->has('aktif') ? (bool) $request->aktif : false;

        if (!$user->hasAnyRole(['admin', 'pengurus_wilayah'])) {
            // Hindari user Daerah mengubah lingkup ke Global atau Daerah lain
            unset($validated['unit_organisasi_id']);
        }

        $kegiatan->update($validated);

        return redirect()->back()->with('success', 'Kegiatan "' . $kegiatan->nama_kegiatan . '" berhasil diperbarui.');
    }

    /**
     * Hapus Kegiatan
     */
    public function destroy(Request $request, string $id)
    {
        $kegiatan = JenisKegiatan::findOrFail($id);
        $user = $request->user();
        
        if ($kegiatan->unit_organisasi_id !== null && $kegiatan->unit_organisasi_id !== $user->unit_organisasi_id) {
            abort(403, 'Unauthorized action.');
        }

        $absensiCount = $kegiatan->absensiKegiatan()->count();

        if ($absensiCount > 0) {
            $kegiatan->update(['aktif' => false]);
            return redirect()->back()->with('success', 'Kegiatan "' . $kegiatan->nama_kegiatan . '" telah dinonaktifkan (memiliki ' . $absensiCount . ' riwayat absensi).');
        }

        $nama = $kegiatan->nama_kegiatan;
        $kegiatan->delete();

        return redirect()->back()->with('success', 'Kegiatan "' . $nama . '" berhasil dihapus.');
    }
}
