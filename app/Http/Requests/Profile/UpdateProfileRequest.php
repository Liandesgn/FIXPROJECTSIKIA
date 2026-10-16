<?php

namespace App\Http\Requests\Profile;

use App\Enums\StatusKehamilanEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        $rules = [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'phone' => ['nullable', 'string', 'max:20'],
        ];

        if ($this->user()?->isOrangTua()) {
            $rules += [
                'nik' => ['nullable', 'string', 'digits:16'],
                'tempat_lahir' => ['nullable', 'string', 'max:255'],
                'tanggal_lahir' => ['nullable', 'date', 'before:today'],
                'status_kehamilan' => ['nullable', Rule::in(StatusKehamilanEnum::values())],
                'hpl' => ['nullable', 'date'],
                'usia_kehamilan' => ['nullable', 'integer', 'min:1', 'max:42'],
            ];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah terdaftar.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit.',
        ];
    }
}
