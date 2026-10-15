<?php

namespace App\Http\Requests\OrangTua;

use App\Enums\StatusKehamilanEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrangTuaRequest extends FormRequest
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
        return [
            // Form 1 — Data Akun
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // Form 2 — Data Ibu
            'nik' => ['required', 'string', 'digits:16'],
            'nama_ibu' => ['required', 'string', 'max:255'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'phone' => ['nullable', 'string', 'max:20'],
            'wilayah_kerja_id' => ['required', 'integer', Rule::exists('wilayah_kerja', 'id')],
            'status_kehamilan' => ['required', Rule::in(StatusKehamilanEnum::values())],
            'hpl' => [
                'nullable',
                'date',
                Rule::requiredIf(fn () => $this->input('status_kehamilan') === StatusKehamilanEnum::Hamil->value),
            ],
            'usia_kehamilan' => [
                'nullable',
                'integer',
                'min:1',
                'max:42',
                Rule::requiredIf(fn () => $this->input('status_kehamilan') === StatusKehamilanEnum::Hamil->value),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit.',
            'wilayah_kerja_id.required' => 'Wilayah (desa) wajib dipilih.',
            'hpl.required' => 'HPL wajib diisi untuk status kehamilan Hamil.',
            'usia_kehamilan.required' => 'Usia kehamilan wajib diisi untuk status kehamilan Hamil.',
        ];
    }
}
