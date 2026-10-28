<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePetugasRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'regex:/^[0-9]+$/', 'min:10', 'max:13'],
            'wilayah_kerja_id' => ['required', 'integer', Rule::exists('wilayah_kerja', 'id')],
        ];

        // Satu bidan penanggung jawab per wilayah (kader boleh banyak).
        if ($this->routeIs('admin.bidan.*')) {
            $rules['wilayah_kerja_id'][] = Rule::unique('bidan', 'wilayah_kerja_id');
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
