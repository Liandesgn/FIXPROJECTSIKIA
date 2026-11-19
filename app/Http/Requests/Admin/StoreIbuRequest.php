<?php

namespace App\Http\Requests\Admin;

use App\Enums\StatusKehamilanEnum;
use App\Models\User;
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
        // Route {ibu} menggunakan model User.
        $user = $this->route('ibu');

        $userId = $user instanceof User ? $user->getKey() : null;
        $profilIbuId = $user instanceof User
            ? $user->ibu?->getKey()
            : null;

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
                Rule::unique('users', 'email')->ignore($userId, 'id'),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'max:8',
            ],

            'phone' => [
                'nullable',
                'digits_between:10,13',
            ],

            'nik' => [
                'required',
                'string',
                'digits:16',
                Rule::unique('ibu', 'nik')->ignore($profilIbuId, 'id'),
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

            'rt_id' => [
                'required',
                'integer',
                Rule::exists('rt', 'id'),
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
            'name.required' => 'Nama wajib diisi.',
            'name.regex' => 'Nama hanya boleh berisi huruf dan spasi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'password.min' => 'Password harus tepat 8 karakter.',
            'password.max' => 'Password harus tepat 8 karakter.',
            'phone.digits_between' => 'Nomor HP harus terdiri dari 10 sampai 13 digit.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit.',
            'nik.unique' => 'NIK sudah digunakan ibu lain.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tgl_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tgl_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'rt_id.required' => 'RT wajib dipilih.',
            'rt_id.exists' => 'RT yang dipilih tidak tersedia.',
            'status_kehamilan.required' => 'Status kehamilan wajib dipilih.',
            'usia_kehamilan.required_if' => 'Usia kehamilan wajib diisi untuk ibu hamil.',
        ];
    }
}