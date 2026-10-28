<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PosyanduRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('posyandu')?->id;

        return [
            'nama_posyandu' => [
                'required',
                'string',
                'max:100',
                Rule::unique('posyandu', 'nama_posyandu')->ignore($id),
            ],

            'wilayah_kerja_id' => [
                'required',
                'integer',
                Rule::exists('wilayah_kerja', 'id'),
            ],

            'kader_id' => [
                'nullable',
                'integer',
                Rule::exists('kader', 'id'),
            ],

            'alamat' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_posyandu.required' => 'Nama Posyandu wajib diisi.',
            'nama_posyandu.unique' => 'Nama Posyandu sudah digunakan.',
            'wilayah_kerja_id.required' => 'Wilayah wajib dipilih.',
            'wilayah_kerja_id.exists' => 'Wilayah kerja tidak ditemukan.',
            'kader_id.exists' => 'Kader tidak ditemukan.',
            'alamat.required' => 'Alamat wajib diisi.',
        ];
    }
}