<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePetugasRequest extends FormRequest
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
        $userId = $this->route('bidan')?->id ?? $this->route('kader')?->id;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'regex:/^[0-9]+$/', 'min:10', 'max:13'],
            'wilayah_kerja_id' => ['required', 'integer', Rule::exists('wilayah_kerja', 'id')],
        ];

        if ($this->routeIs('admin.bidan.*')) {
            // Baris bidan milik akun ini diabaikan supaya wilayahnya sendiri tidak dianggap ganda.
            $rules['wilayah_kerja_id'][] = Rule::unique('bidan', 'wilayah_kerja_id')
                ->ignore($this->route('bidan')?->bidan?->id);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah terdaftar.',
            'wilayah_kerja_id.required' => 'Wilayah kerja wajib dipilih.',
            'wilayah_kerja_id.unique' => 'Wilayah ini sudah memiliki bidan penanggung jawab.',
        ];
    }
}
