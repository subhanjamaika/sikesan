<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * Tampilkan daftar Program berdasarkan Daerah (unit_organisasi_id)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);
        $daerahs = collect();
        
        $query = Program::with('unitOrganisasi');

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

        $programs = $query->latest()->get();

        return view('program.index', compact('programs', 'daerahs'));
    }

    /**
     * Simpan Program Baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_program'       => 'required|string|max:255',
            'unit_organisasi_id' => 'nullable|exists:unit_organisasi,id',
        ]);

        $user = $request->user();
        if (!$user->hasAnyRole(['admin', 'pengurus_wilayah'])) {
            // Paksa ke daerah milik user jika bukan Admin/Wilayah
            $validated['unit_organisasi_id'] = $user->unit_organisasi_id;
        }

        $program = Program::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Program berhasil ditambahkan.',
                'data'    => $program,
            ]);
        }

        return redirect()->back()->with('success', 'Program "' . $program->nama_program . '" berhasil ditambahkan.');
    }

    /**
     * Perbarui Program
     */
    public function update(Request $request, Program $program)
    {
        $user = $request->user();
        
        // Cek izin (jika bukan admin/wilayah, hanya boleh ubah program daerah sendiri)
        if (!$user->hasAnyRole(['admin', 'pengurus_wilayah'])) {
            if ($program->unit_organisasi_id !== null && $program->unit_organisasi_id !== $user->unit_organisasi_id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $validated = $request->validate([
            'nama_program'       => 'required|string|max:255',
            'unit_organisasi_id' => 'nullable|exists:unit_organisasi,id',
        ]);

        if (!$user->hasAnyRole(['admin', 'pengurus_wilayah'])) {
            // Hindari user Daerah mengubah lingkup ke Global atau Daerah lain
            unset($validated['unit_organisasi_id']);
        }

        $program->update($validated);

        return redirect()->back()->with('success', 'Program "' . $program->nama_program . '" berhasil diperbarui.');
    }

    /**
     * Hapus Program
     */
    public function destroy(Request $request, Program $program)
    {
        $user = $request->user();
        
        if (!$user->hasAnyRole(['admin', 'pengurus_wilayah'])) {
            if ($program->unit_organisasi_id !== null && $program->unit_organisasi_id !== $user->unit_organisasi_id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $nama = $program->nama_program;
        $program->delete();

        return redirect()->back()->with('success', 'Program "' . $nama . '" berhasil dihapus.');
    }
}
