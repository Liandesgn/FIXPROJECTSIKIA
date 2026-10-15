<?php

namespace App\Http\Requests\User;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(RoleEnum::manageable())],
            'status' => ['nullable', Rule::in(UserStatusEnum::values())],
            'wilayah_kerja_id' => [
                'nullable',
                'integer',
                Rule::exists('wilayah_kerja', 'id'),
                Rule::requiredIf(fn () => in_array($this->input('role'), [
                    RoleEnum::Bidan->value,
                    RoleEnum::Kader->value,
                ], true)),
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
            'role.in' => 'Role tidak valid.',
            'wilayah_kerja_id.required_if' => 'Wilayah kerja wajib diisi untuk Bidan dan Kader.',
        ];
    }
}
