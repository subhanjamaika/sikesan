<?php

namespace App\Http\Requests;

use App\Models\AbsensiPuasa;
use Illuminate\Foundation\Http\FormRequest;

class StoreAbsensiPuasaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('create', \App\Models\AbsensiPuasa::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'tanggal' => [
                'required', 
                'date',
                function ($attribute, $value, $fail) {
                    if (!AbsensiPuasa::isHariPuasaValid($value)) {
                        $fail('Absensi puasa hanya berlaku untuk hari Senin dan Kamis.');
                    }
                },
            ],
            'absensi' => ['required', 'array'],
            'absensi.*.santri_id' => ['required', 'exists:santri,id'],
            'absensi.*.status_puasa' => ['required', 'in:puasa,tidak_puasa,haid,sakit,izin'],
            'absensi.*.keterangan' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal absensi wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'absensi.required' => 'Daftar absensi wajib diisi.',
            'absensi.array' => 'Daftar absensi harus berupa array.',
            'absensi.*.santri_id.required' => 'ID Santri wajib diisi pada setiap data absensi.',
            'absensi.*.santri_id.exists' => 'Data santri tidak ditemukan di dalam sistem.',
            'absensi.*.status_puasa.required' => 'Status puasa santri wajib diisi.',
            'absensi.*.status_puasa.in' => 'Status puasa tidak valid.',
            'absensi.*.keterangan.string' => 'Keterangan harus berupa teks.',
        ];
    }
}
