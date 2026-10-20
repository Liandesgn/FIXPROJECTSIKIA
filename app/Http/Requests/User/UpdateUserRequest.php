<?php

namespace App\Http\Requests\User;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user')?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['sometimes', 'required', Rule::in(RoleEnum::manageable())],
            'status' => ['sometimes', Rule::in(UserStatusEnum::values())],
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
            'role.in' => 'Role tidak valid.',
            'wilayah_kerja_id.required_if' => 'Wilayah kerja wajib diisi untuk Bidan dan Kader.',
        ];
    }
}
