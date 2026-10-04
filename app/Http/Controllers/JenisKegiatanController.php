<?php

namespace App\Http\Controllers;

use App\Models\JenisKegiatan;
use Illuminate\Http\Request;

class JenisKegiatanController extends Controller
{
    /**
     * Tampilkan daftar Master Jenis Kegiatan.
     */
    public function index()
    {
        $jenisKegiatanList = JenisKegiatan::withCount('absensiKegiatan')
            ->get();

        return view('jenis-kegiatan.index', compact('jenisKegiatanList'));
    }

    /**
     * Simpan Jenis Kegiatan Baru (Dinamis).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kegiatan'   => 'required|string|max:255',
            'jam_pelaksanaan' => 'nullable|date_format:H:i',
            'urutan'          => 'nullable|integer',
            'aktif'           => 'nullable|boolean',
            'program_id'      => 'nullable|string',
        ]);

        if (empty($validated['urutan'])) {
            $maxUrutan = JenisKegiatan::withoutGlobalScopes()->max('urutan') ?? 0;
            $validated['urutan'] = $maxUrutan + 1;
        }

        $validated['aktif'] = $request->has('aktif') ? (bool) $request->aktif : true;

        if (!empty($validated['program_id'])) {
            $validated['program_id'] = preg_replace("/[^a-z0-9_']+/", '_', strtolower(trim($validated['program_id'])));
            $validated['program_id'] = trim($validated['program_id'], '_');
        }

        $jenisKegiatan = JenisKegiatan::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Jenis Kegiatan berhasil ditambahkan.',
                'data'    => $jenisKegiatan,
            ]);
        }

        return redirect()->back()->with('success', 'Jenis kegiatan "' . $jenisKegiatan->nama_kegiatan . '" berhasil ditambahkan.');
    }

    /**
     * Perbarui Jenis Kegiatan.
     */
    public function update(Request $request, JenisKegiatan $jenisKegiatan)
    {
        $validated = $request->validate([
            'nama_kegiatan'   => 'required|string|max:255',
            'jam_pelaksanaan' => 'nullable|date_format:H:i',
            'urutan'          => 'nullable|integer',
            'aktif'           => 'nullable|boolean',
            'program_id'      => 'nullable|string',
        ]);

        $validated['aktif'] = $request->has('aktif') ? (bool) $request->aktif : false;

        if (!empty($validated['program_id'])) {
            $validated['program_id'] = preg_replace("/[^a-z0-9_']+/", '_', strtolower(trim($validated['program_id'])));
            $validated['program_id'] = trim($validated['program_id'], '_');
        }

        $jenisKegiatan->update($validated);

        return redirect()->back()->with('success', 'Jenis kegiatan "' . $jenisKegiatan->nama_kegiatan . '" berhasil diperbarui.');
    }

    /**
     * Hapus atau Nonaktifkan Jenis Kegiatan.
     */
    public function destroy(JenisKegiatan $jenisKegiatan)
    {
        $absensiCount = $jenisKegiatan->absensiKegiatan()->count();

        if ($absensiCount > 0) {
            $jenisKegiatan->update(['aktif' => false]);
            return redirect()->back()->with('success', 'Jenis kegiatan "' . $jenisKegiatan->nama_kegiatan . '" telah dinonaktifkan (memiliki ' . $absensiCount . ' riwayat absensi).');
        }

        $nama = $jenisKegiatan->nama_kegiatan;
        $jenisKegiatan->delete();

        return redirect()->back()->with('success', 'Jenis kegiatan "' . $nama . '" berhasil dihapus.');
    }
}
