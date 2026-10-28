<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnakRequest extends FormRequest
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
        $id = $this->route('anak')?->id;

        return [
            'ibu_id' => ['required', 'integer', Rule::exists('ibu', 'id')],
            'nik_anak' => ['required', 'string', 'digits:16', Rule::unique('anak', 'nik_anak')->ignore($id)],
            'nama_anak' => ['required', 'string', 'max:100'],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'nama_ayah' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ibu_id.required' => 'Ibu wajib dipilih.',
            'ibu_id.exists' => 'Data ibu tidak ditemukan.',
            'nik_anak.required' => 'NIK anak wajib diisi.',
            'nik_anak.digits' => 'NIK anak harus terdiri dari 16 digit.',
            'nik_anak.unique' => 'NIK anak sudah terdaftar.',
            'nama_anak.required' => 'Nama anak wajib diisi.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh setelah hari ini.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'nama_ayah.required' => 'Nama ayah wajib diisi.',
        ];
    }
}