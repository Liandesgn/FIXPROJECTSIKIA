<?php

namespace App\Http\Requests\Admin;

use App\Models\Kader;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePetugasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $puskesmasId = $this->user()?->managedPuskesmasId();

        $rules = [
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
                Rule::unique('users', 'email'),
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'max:8',
            ],

            'phone' => [
                'required',
                'digits_between:10,13',
                'regex:/^[0-9]+$/',
            ],

            'wilayah_kerja_id' => [
                'required',
                'integer',
                Rule::exists('wilayah_kerja', 'id')
                    ->where('puskesmas_id', $puskesmasId),
            ],
        ];

        // Satu Kelurahan/Desa hanya boleh memiliki satu Bidan.
        if ($this->routeIs('admin.bidan.*')) {
            $rules['wilayah_kerja_id'][] = Rule::unique(
                'bidan',
                'wilayah_kerja_id'
            );
        }

        return $rules;
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (
                    ! $this->routeIs('admin.kader.*')
                    || $validator->errors()->isNotEmpty()
                ) {
                    return;
                }

                $wilayahId = (int) $this->input('wilayah_kerja_id');

                // Maksimal 5 Kader dalam satu Kelurahan/Desa.
                if (
                    Kader::where('wilayah_kerja_id', $wilayahId)->count() >= 5
                ) {
                    $validator->errors()->add(
                        'wilayah_kerja_id',
                        'Wilayah ini sudah mencapai batas maksimal 5 kader.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.regex' => 'Nama hanya boleh berisi huruf dan spasi. Angka dan simbol tidak diperbolehkan.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email tersebut sudah terdaftar. Silakan gunakan email lain.',

            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password harus tepat 8 karakter.',
            'password.max' => 'Password harus tepat 8 karakter.',

            'phone.required' => 'Nomor HP wajib diisi.',
            'phone.digits_between' => 'Nomor HP harus terdiri dari 10 sampai 13 digit.',
            'phone.regex' => 'Nomor HP hanya boleh berisi angka.',

            'wilayah_kerja_id.required' => 'Wilayah kerja wajib dipilih.',
            'wilayah_kerja_id.unique' => 'Wilayah ini sudah memiliki bidan penanggung jawab.',
            'wilayah_kerja_id.exists' => 'Wilayah yang dipilih tidak tersedia atau bukan bagian dari Puskesmas Anda.',
        ];
    }
}
