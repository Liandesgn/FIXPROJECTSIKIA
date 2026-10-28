<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WilayahKerjaRequest extends FormRequest
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
        // Saat edit, $this->route('wilayah') berisi wilayah yang sedang diedit.
        // Dipakai agar nama miliknya sendiri tidak dianggap duplikat.
        $id = $this->route('wilayah')?->id;

        return [
            'nama_wilayah' => [
                'required', 'string', 'max:100',
                Rule::unique('wilayah_kerja', 'nama_wilayah')->ignore($id),
            ],
            'desa' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_wilayah.required' => 'Nama wilayah wajib diisi.',
            'nama_wilayah.unique' => 'Nama wilayah sudah digunakan.',
            'nama_wilayah.max' => 'Nama wilayah maksimal 100 karakter.',
            'desa.required' => 'Kelurahan/desa wajib diisi.',
            'kecamatan.required' => 'Kecamatan wajib diisi.',
        ];
    }
}