<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\AspekPenilaian;

class StorePenilaianKeseharianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization ditangani oleh Policy
    }

    public function rules(): array
    {
        $user = $this->user();
        $allowedUnitIds = $this->attributes->get('allowedUnitIds', []);
        
        $aspekQuery = AspekPenilaian::query();
        if ($user && ($user->hasRole('pengurus_wilayah') || (!$user->hasAnyRole(['admin', 'dewan_pemangku'])))) {
            $aspekQuery->where(function ($q) use ($allowedUnitIds) {
                $q->whereIn('unit_organisasi_id', $allowedUnitIds)
                  ->orWhereNull('unit_organisasi_id');
            });
        }
        $aspekIds = $aspekQuery->pluck('id')->toArray();

        $rules = [
            'nama_santri' => ['required', 'string'],
            'santri_id' => ['required', 'uuid', 'exists:santri,id'],
            'periode' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'tanggal_penilaian' => ['required', 'date'],
            'catatan_kelebihan' => ['required', 'string'],
            'catatan_peningkatan' => ['required', 'string'],
            'rencana_tindak_lanjut' => ['required', 'string'],
            'skor' => ['required', 'array', 'size:' . count($aspekIds)],
        ];

        // Validasi setiap skor per aspek: wajib diisi, antara 1-4
        foreach ($aspekIds as $aspekId) {
            $rules["skor.{$aspekId}"] = ['required', 'integer', 'between:1,4'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nama_santri.required' => 'Nama/NIS Santri wajib diisi.',
            'santri_id.required' => 'Santri tidak ditemukan atau ditemukan lebih dari satu dengan nama tersebut. Mohon ketik nama yang lebih spesifik atau gunakan NIS.',
            'skor.required' => 'Semua skor penilaian wajib diisi.',
            'skor.size' => 'Jumlah aspek penilaian tidak sesuai. Pastikan semua 15 aspek terisi.',
            'skor.*.required' => 'Skor untuk aspek ini wajib diisi.',
            'skor.*.between' => 'Skor harus antara 1 (Perlu Pembinaan) sampai 4 (Sangat Baik).',
            'periode.regex' => 'Format periode harus YYYY-MM (contoh: 2026-07).',
            'tanggal_penilaian.required' => 'Tanggal penilaian wajib diisi.',
            'catatan_kelebihan.required' => 'Catatan kelebihan wajib diisi.',
            'catatan_peningkatan.required' => 'Catatan peningkatan wajib diisi.',
            'rencana_tindak_lanjut.required' => 'Rencana tindak lanjut wajib diisi.',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('nama_santri') && !empty($this->nama_santri)) {
            $query = trim($this->input('nama_santri'));
            $santriList = \App\Models\Santri::where('nama', 'like', '%' . $query . '%')
                                            ->orWhere('nis_induk', $query)
                                            ->get();

            if ($santriList->count() === 1) {
                $this->merge([
                    'santri_id' => $santriList->first()->id,
                ]);
            }
        }
    }
}
