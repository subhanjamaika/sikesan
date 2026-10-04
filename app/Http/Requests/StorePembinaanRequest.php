<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePembinaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization ditangani oleh Policy
    }

    public function rules(): array
    {
        return [
            'pelanggaran_id' => ['required', 'uuid', 'exists:pelanggaran,id'],
            'jenis_pembinaan' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'hasil' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_pembinaan.required' => 'Jenis pembinaan wajib diisi.',
            'pelanggaran_id.required' => 'Data pelanggaran tidak ditemukan.',
            'hasil.max' => 'Hasil pembinaan maksimal 2000 karakter.',
        ];
    }

    /**
     * Helper text untuk field 'hasil':
     * Field ini disarankan diisi karena data hasil pembinaan
     * akan ditampilkan di Sistem 1 untuk evaluasi santri.
     */
    public function attributes(): array
    {
        return [
            'hasil' => 'hasil pembinaan (disarankan diisi — tampil di Sistem 1)',
        ];
    }
}
