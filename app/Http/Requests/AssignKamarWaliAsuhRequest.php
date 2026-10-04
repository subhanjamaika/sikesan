<?php

namespace App\Http\Requests;

use App\Models\PenugasanWaliAsuh;
use Illuminate\Foundation\Http\FormRequest;

class AssignKamarWaliAsuhRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id'     => ['required', 'exists:users,id'],
            'kamar_ids'   => ['required', 'array'],
            'kamar_ids.*' => ['exists:unit_organisasi,id'],
        ];
    }

    /**
     * Custom validation after rules to enforce exclusive kamar assignment.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $userId = $this->input('user_id');
            $kamarIds = $this->input('kamar_ids', []);

            if (empty($userId) || !is_array($kamarIds)) {
                return;
            }

            // Cek kamar_id yang sudah ditugaskan ke Wali Asuh lain
            $existingAssignments = PenugasanWaliAsuh::with(['kamar', 'user'])
                ->whereIn('kamar_id', $kamarIds)
                ->where('user_id', '!=', $userId)
                ->get();

            foreach ($existingAssignments as $penugasan) {
                $namaKamar = $penugasan->kamar->nama ?? $penugasan->kamar_id;
                $validator->errors()->add('kamar_ids', "Kamar {$namaKamar} sudah ditugaskan kepada Wali Asuh lain.");
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required'   => 'Wali Asuh wajib dipilih.',
            'user_id.exists'     => 'Data Wali Asuh tidak valid.',
            'kamar_ids.required' => 'Kamar wajib dipilih.',
            'kamar_ids.array'    => 'Data kamar harus berupa array.',
            'kamar_ids.*.exists' => 'Salah satu kamar yang dipilih tidak valid.',
        ];
    }
}
