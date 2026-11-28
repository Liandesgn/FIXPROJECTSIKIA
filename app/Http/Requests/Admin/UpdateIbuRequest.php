<?php

namespace App\Http\Requests\Admin;

use App\Enums\StatusKehamilanEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIbuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Parameter route {ibu} adalah model Ibu, bukan User.
        $ibu = $this->route('ibu');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\pL]+(?: [\pL]+)*$/u',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($ibu?->user_id),
            ],

            'phone' => [
                'nullable',
                'digits_between:10,13',
            ],

            'nik' => [
                'required',
                'string',
                'digits:16',
                Rule::unique('ibu', 'nik')->ignore($ibu?->id),
            ],

            'tempat_lahir' => [
                'required',
                'string',
                'max:255',
            ],

            'tgl_lahir' => [
                'required',
                'date',
                'before:today',
            ],

            'alamat_lengkap' => [
            'nullable',
            'string',
            'max:1000',
            ],

            'wilayah_kerja_id' => [
                'required',
                'integer',
                Rule::exists('wilayah_kerja', 'id'),
            ],

            'status_kehamilan' => [
                'required',
                Rule::in(StatusKehamilanEnum::values()),
            ],

            'usia_kehamilan' => [
                'nullable',
                'required_if:status_kehamilan,hamil',
                'integer',
                'min:1',
                'max:42',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama ibu wajib diisi.',
            'name.regex' => 'Nama hanya boleh berisi huruf dan spasi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'phone.digits_between' => 'Nomor HP harus terdiri dari 10 sampai 13 digit.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tgl_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tgl_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'wilayah_kerja_id.required' => 'Wilayah kerja wajib dipilih.',
            'wilayah_kerja_id.exists' => 'Wilayah kerja yang dipilih tidak ditemukan.',
            'status_kehamilan.required' => 'Status kehamilan wajib dipilih.',
            'usia_kehamilan.required_if' => 'Usia kehamilan wajib diisi jika status ibu hamil.',
            'usia_kehamilan.min' => 'Usia kehamilan minimal 1 minggu.',
            'usia_kehamilan.max' => 'Usia kehamilan maksimal 42 minggu.',
        ];
    }
}
