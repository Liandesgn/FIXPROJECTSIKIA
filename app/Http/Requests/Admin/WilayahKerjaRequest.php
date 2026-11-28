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
        return [
            'kecamatan_id' => [
                'required',
                'integer',
                'exists:kecamatan,id',
            ],

            'puskesmas_id' => [
                'required',
                'integer',
                Rule::exists('puskesmas', 'id')
                    ->where('kecamatan_id', $this->input('kecamatan_id')),
            ],

            'kelurahan_desa_id' => [
                'required',
                'integer',
                Rule::exists('kelurahan_desa', 'id')
                    ->where('kecamatan_id', $this->input('kecamatan_id'))
                    ->where('puskesmas_id', $this->input('puskesmas_id')),
            ],

            'rw_id' => [
                'required',
                'integer',
                Rule::exists('rw', 'id')
                    ->where('kelurahan_desa_id', $this->input('kelurahan_desa_id')),
                Rule::unique('wilayah_kerja', 'rw_id')
                    ->ignore($this->route('wilayah')),
            ],

            'deskripsi' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
