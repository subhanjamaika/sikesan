<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKegiatan;
use App\Models\AbsensiPuasa;
use App\Models\Pelanggaran;
use App\Models\PenilaianKeseharian;
use App\Models\Santri;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard dengan ringkasan per role.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load('role');
        $role = $user->role->nama_role;

        $data = compact('user', 'role');

        switch ($role) {
            case 'admin':
            case 'pengurus_wilayah':
            case 'dewan_pemangku':
                $data['total_santri'] = Santri::count();
                $data['total_absensi_hari_ini'] = AbsensiKegiatan::where('tanggal', now()->toDateString())->count();
                $data['total_pelanggaran_bulan_ini'] = Pelanggaran::whereMonth('tanggal', now()->month)
                    ->whereYear('tanggal', now()->year)->count();
                $data['total_penilaian_bulan_ini'] = PenilaianKeseharian::where('periode', now()->format('Y-m'))->count();
                $data['pelanggaran_terbaru'] = Pelanggaran::with('santri')
                    ->latest()->take(5)->get();
                $data['pelanggaran_per_kategori'] = $this->getPelanggaranPerKategori();
                break;

            case 'pengurus_daerah':
                $unitScope = $user->getUnitScope();
                $santriIds = Santri::inUnitOrProgram($unitScope)->pluck('id');
                $data['total_santri'] = $santriIds->count();
                $data['total_absensi_hari_ini'] = AbsensiKegiatan::whereIn('santri_id', $santriIds)
                    ->where('tanggal', now()->toDateString())->count();
                $data['total_pelanggaran_bulan_ini'] = Pelanggaran::whereIn('santri_id', $santriIds)
                    ->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->count();
                break;

            case 'pengurus_sektor':
                $allowedUnitIds = $user->getAllowedUnitIds();
                $santriAsuhan = Santri::whereIn('unit_organisasi_id', $allowedUnitIds)->get();
                $santriIds = $santriAsuhan->pluck('id');

                $data['santri_asuhan'] = $santriAsuhan;
                $data['total_santri_asuhan'] = $santriAsuhan->count();
                $data['absensi_kegiatan_hari_ini'] = AbsensiKegiatan::with(['santri', 'jenisKegiatan'])
                    ->whereIn('santri_id', $santriIds)
                    ->where('tanggal', now()->toDateString())
                    ->get();
                $data['pelanggaran_santri'] = Pelanggaran::whereIn('santri_id', $santriIds)
                    ->latest()->take(5)->get();
                $data['total_pelanggaran_bulan_ini'] = Pelanggaran::whereIn('santri_id', $santriIds)
                    ->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->count();
                $data['rata_rata_nilai'] = PenilaianKeseharian::whereIn('santri_id', $santriIds)
                    ->where('periode', now()->format('Y-m'))
                    ->avg('nilai_akhir');
                $data['penilaian_terbaru'] = PenilaianKeseharian::with('santri')
                    ->whereIn('santri_id', $santriIds)->latest()->take(5)->get();
                break;

            case 'wali_santri':
                $santriId = $user->santri_id;
                $santri = $santriId ? Santri::find($santriId) : null;
                $data['santri_wali'] = $santri;
                if ($santriId) {
                    $data['absensi_kegiatan_terbaru'] = AbsensiKegiatan::with('jenisKegiatan')
                        ->where('santri_id', $santriId)
                        ->latest('tanggal')->take(5)->get();
                    $data['penilaian_anak'] = PenilaianKeseharian::where('santri_id', $santriId)
                        ->latest()->take(5)->get();
                }
                break;
        }

        return view('dashboard', $data);
    }

    /**
     * Data pelanggaran per kategori untuk chart.
     */
    private function getPelanggaranPerKategori(): array
    {
        return [
            'umum' => Pelanggaran::where('kategori', 'umum')->count(),
            'devisi' => Pelanggaran::where('kategori', 'devisi')->count(),
        ];
    }
}
