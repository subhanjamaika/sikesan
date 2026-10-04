<?php

namespace App\Http\Requests;

use App\Models\Santri;
use Illuminate\Foundation\Http\FormRequest;

class StoreAbsensiKegiatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('create', \App\Models\AbsensiKegiatan::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'jenis_kegiatan_id' => ['required', 'exists:jenis_kegiatan,id'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'absensi' => ['required', 'array'],
            'absensi.*.santri_id' => ['required', 'exists:santri,id'],
            'absensi.*.status' => ['required', 'in:hadir,izin,sakit,alpa'],
            'absensi.*.keterangan' => ['nullable', 'string'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            
            // Ambil data kegiatan
            $jenisKegiatanId = $this->input('jenis_kegiatan_id');
            $kegiatan = \App\Models\JenisKegiatan::find($jenisKegiatanId);
            
            $validUnitIds = [];
            $isLokal = $kegiatan && !is_null($kegiatan->unit_organisasi_id);

            if ($isLokal) {
                $kegiatanUnitId = $kegiatan->unit_organisasi_id;
                $childIds = \App\Models\UnitOrganisasi::where('parent_id', $kegiatanUnitId)->pluck('id')->toArray();
                $validUnitIds = array_merge([$kegiatanUnitId], $childIds);
                
                // Validasi Keamanan Backend: Kecocokan Santri dengan Kegiatan Lokal
                $absensi = $this->input('absensi', []);
                if (!empty($absensi)) {
                    $santriIds = array_column($absensi, 'santri_id');
                    $invalidSantris = Santri::whereIn('id', $santriIds)
                        ->whereNotIn('unit_organisasi_id', $validUnitIds)
                        ->count();
                        
                    if ($invalidSantris > 0) {
                        $validator->errors()->add('absensi', 'Terdapat santri yang tidak berhak mengikuti kegiatan lokal ini berdasarkan daerah/kamarnya.');
                    }
                }
            }

            // Ambil semua ID unit_organisasi wewenang user (termasuk child)
            $allowedUnitIds = $user->getAllowedUnitIds();
            $targetUnitIds = $allowedUnitIds;

            // Jika kegiatan lokal dan user punya akses bebas (Admin), paksa scope targetUnitIds
            if ($isLokal && $user->hasAnyRole(['admin', 'superadmin'])) {
                $targetUnitIds = $validUnitIds;
            }

            // Hitung total santri yang berada di bawah wewenang user
            $santriQuery = Santri::query();
            
            if (!empty($targetUnitIds)) {
                $santriQuery->whereIn('unit_organisasi_id', $targetUnitIds);
            }
            
            // Filter tambahan dari Program Kegiatan (jika kegiatan memiliki program spesifik)
            if ($kegiatan && !is_null($kegiatan->program_id)) {
                $santriQuery->where('program_id', $kegiatan->program_id);
            }
            
            // Jika request membawa parameter filter dari form, sesuaikan kuerinya
            if ($this->filled('daerah_id') && !$this->filled('kamar_id')) {
                $daerahId = $this->input('daerah_id');
                $childIds = \App\Models\UnitOrganisasi::where('parent_id', $daerahId)->pluck('id')->toArray();
                $santriQuery->whereIn('unit_organisasi_id', array_merge([$daerahId], $childIds));
            }
            if ($this->filled('kamar_id')) {
                $santriQuery->where('unit_organisasi_id', $this->input('kamar_id'));
            }
            if ($this->filled('search')) {
                $search = $this->input('search');
                $santriQuery->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nis_induk', 'like', "%{$search}%");
                });
            }

            $totalSantri = $santriQuery->count();

            // Jumlah santri yang diabsen
            $jumlahDiabsen = count($this->input('absensi', []));

            if ($jumlahDiabsen !== $totalSantri) {
                $validator->errors()->add('absensi', 'Masih ada santri yang belum diisi statusnya, harap lengkapi semua sebelum menyimpan.');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'jenis_kegiatan_id.required' => 'Jenis kegiatan wajib dipilih.',
            'jenis_kegiatan_id.exists' => 'Jenis kegiatan yang dipilih tidak valid.',
            'tanggal.required' => 'Tanggal kegiatan wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tanggal.before_or_equal' => 'Tanggal kegiatan tidak boleh di masa depan.',
            'absensi.required' => 'Daftar absensi wajib diisi.',
            'absensi.array' => 'Daftar absensi harus berupa array.',
            'absensi.*.santri_id.required' => 'ID Santri wajib diisi pada setiap data absensi.',
            'absensi.*.santri_id.exists' => 'Data santri tidak ditemukan di dalam sistem.',
            'absensi.*.status.required' => 'Status kehadiran santri wajib diisi.',
            'absensi.*.status.in' => 'Status kehadiran hanya boleh berisi: hadir, izin, sakit, atau alpa.',
            'absensi.*.keterangan.string' => 'Keterangan harus berupa teks.',
        ];
    }
}
