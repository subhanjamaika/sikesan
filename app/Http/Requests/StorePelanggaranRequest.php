<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePelanggaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization ditangani oleh Policy
    }

    public function rules(): array
    {
        return [
            'santri_id' => ['required', 'uuid', 'exists:santri,id'],
            'kategori' => ['required', Rule::in(['umum', 'devisi'])],
            'deskripsi' => ['required', 'string', 'min:10'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'tingkat' => ['required', Rule::in(['sektor', 'daerah'])],
        ];
    }

    public function messages(): array
    {
        return [
            'deskripsi.required' => 'Deskripsi pelanggaran wajib diisi.',
            'deskripsi.min' => 'Deskripsi pelanggaran minimal 10 karakter.',
            'tanggal.before_or_equal' => 'Tanggal pelanggaran tidak boleh di masa depan.',
            'santri_id.required' => 'Santri wajib dipilih.',
        ];
    }
}
