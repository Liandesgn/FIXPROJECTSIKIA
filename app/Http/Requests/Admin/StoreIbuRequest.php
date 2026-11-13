<?php

namespace App\Http\Requests\Admin;

use App\Enums\StatusKehamilanEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIbuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'string', 'min:8', 'max:8'],
            'nik' => [
                'required',
                'string',
                'digits:16',
                Rule::unique('ibu', 'nik'),
            ],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tgl_lahir' => ['required', 'date', 'before:today'],
            'phone' => ['required', 'digits_between:10,13', 'regex:/^[0-9]+$/'],
            'wilayah_kerja_id' => [
                'required',
                'integer',
                Rule::exists('wilayah_kerja', 'id'),
            ],
            'status_kehamilan' => [
                'required',
                Rule::in(StatusKehamilanEnum::values()),
            ],
            'hpl' => [
                'nullable',
                'date',
                Rule::requiredIf(
                    fn () => $this->input('status_kehamilan') === StatusKehamilanEnum::Hamil->value
                ),
            ],
            'usia_kehamilan' => [
                'nullable',
                'integer',
                'min:1',
                'max:42',
                Rule::requiredIf(
                    fn () => $this->input('status_kehamilan') === StatusKehamilanEnum::Hamil->value
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email tersebut sudah terdaftar. Silakan gunakan email lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password harus tepat 8 karakter.',
            'password.max' => 'Password harus tepat 8 karakter.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tgl_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tgl_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'phone.digits_between' => 'Nomor HP harus terdiri dari 10 sampai 13 digit.',
            'phone.regex' => 'Nomor HP hanya boleh berisi angka.',
            'wilayah_kerja_id.required' => 'Wilayah (desa) wajib dipilih.',
            'wilayah_kerja_id.exists' => 'Wilayah kerja yang dipilih tidak tersedia.',
            'status_kehamilan.required' => 'Status kehamilan wajib dipilih.',
            'hpl.required' => 'HPL wajib diisi untuk status kehamilan Hamil.',
            'usia_kehamilan.required' => 'Usia kehamilan wajib diisi untuk status kehamilan Hamil.',
        ];
    }
}
