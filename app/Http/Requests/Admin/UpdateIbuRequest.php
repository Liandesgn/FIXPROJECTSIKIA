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

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $ibu = $this->route('ibu'); // model Ibu dari alamat data-ibu/{ibu}

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ibu?->user_id)],
            'phone' => ['nullable', 'regex:/^[0-9]+$/', 'min:10', 'max:13'],
            'nik' => ['required', 'string', 'digits:16', Rule::unique('ibu', 'nik')->ignore($ibu?->id)],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'status_kehamilan' => ['required', Rule::in(StatusKehamilanEnum::values())],
            'usia_kehamilan' => ['nullable', 'required_if:status_kehamilan,hamil', 'integer', 'min:1', 'max:45'],
            'hpl' => ['nullable', 'required_if:status_kehamilan,hamil', 'date'],
            'wilayah_kerja_id' => ['required', 'integer', Rule::exists('wilayah_kerja', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.regex' => 'No. telp hanya boleh berisi angka.',
            'phone.min' => 'No. telp minimal 10 digit.',
            'phone.max' => 'No. telp maksimal 13 digit.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit.',
            'nik.unique' => 'NIK sudah digunakan ibu lain.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'status_kehamilan.required' => 'Status kehamilan wajib dipilih.',
            'usia_kehamilan.required_if' => 'Usia kehamilan wajib diisi untuk ibu hamil.',
            'hpl.required_if' => 'HPL wajib diisi untuk ibu hamil.',
            'wilayah_kerja_id.required' => 'Wilayah wajib dipilih.',
        ];
    }
}