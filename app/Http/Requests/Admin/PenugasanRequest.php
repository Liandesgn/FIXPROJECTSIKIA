<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PenugasanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = [
            'bidan_id' => ['required', 'integer', Rule::exists('bidan', 'user_id')],
            'kader_ids' => ['nullable', 'array'],
            'kader_ids.*' => ['integer', Rule::exists('kader', 'user_id')],
        ];

        // Saat tambah, wilayah dipilih di form. Saat edit, wilayah dari alamat URL.
        if (! $this->route('wilayah')) {
            $rules['wilayah_kerja_id'] = ['required', 'integer', Rule::exists('wilayah_kerja', 'id')];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'wilayah_kerja_id.required' => 'Wilayah wajib dipilih.',
            'bidan_id.required' => 'Bidan penanggung jawab wajib dipilih.',
            'bidan_id.exists' => 'Akun bidan penanggung jawab tidak ditemukan.',
            'kader_ids.*.exists' => 'Akun kader wilayah tidak ditemukan.',
        ];
    }
}