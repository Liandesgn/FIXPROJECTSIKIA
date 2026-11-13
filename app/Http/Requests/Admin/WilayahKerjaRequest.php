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

    public function rules(): array
    {
        $id = $this->route('wilayah')?->id;

        return [
            'nama_wilayah' => [
                'required',
                'string',
                'max:100',
                Rule::unique('wilayah_kerja', 'nama_wilayah')->ignore($id),
            ],
            'kelurahan' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_wilayah.required' => 'Nama wilayah wajib diisi.',
            'nama_wilayah.unique' => 'Nama wilayah sudah digunakan.',
            'nama_wilayah.max' => 'Nama wilayah maksimal 100 karakter.',
            'kelurahan.required' => 'Kelurahan/desa wajib diisi.',
            'kecamatan.required' => 'Kecamatan wajib diisi.',
            'deskripsi.max' => 'Deskripsi maksimal 1000 karakter.',
        ];
    }
}
