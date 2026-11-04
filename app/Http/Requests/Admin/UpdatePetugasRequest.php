<?php

namespace App\Http\Requests\Admin;

use App\Models\Kader;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePetugasRequest extends FormRequest
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
        $userId = $this->route('bidan')?->id ?? $this->route('kader')?->id;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'regex:/^[0-9]+$/', 'min:10', 'max:13'],
            'wilayah_kerja_id' => ['required', 'integer', Rule::exists('wilayah_kerja', 'id')],
        ];

        if ($this->routeIs('admin.bidan.*')) {
            $rules['wilayah_kerja_id'][] = Rule::unique('bidan', 'wilayah_kerja_id')
                ->ignore($this->route('bidan')?->bidan?->id);
        }

        $managedWilayahId = $this->user()?->loadMissing('admin')->managedWilayahId();

        if ($managedWilayahId) {
            $rules['wilayah_kerja_id'][] = Rule::in([$managedWilayahId]);
        }

        return $rules;
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->routeIs('admin.kader.*') || $validator->errors()->isNotEmpty()) {
                    return;
                }

                $wilayahId = (int) $this->input('wilayah_kerja_id');
                $currentId = $this->route('kader')?->kader?->id;

                $jumlah = Kader::where('wilayah_kerja_id', $wilayahId)
                    ->when($currentId, fn ($query) => $query->where('id', '!=', $currentId))
                    ->count();

                if ($jumlah >= 3) {
                    $validator->errors()->add(
                        'wilayah_kerja_id',
                        'Wilayah ini sudah memiliki 3 kader.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah terdaftar.',
            'wilayah_kerja_id.required' => 'Wilayah kerja wajib dipilih.',
            'wilayah_kerja_id.unique' => 'Wilayah ini sudah memiliki bidan penanggung jawab.',
            'wilayah_kerja_id.in' => 'Admin hanya boleh mengelola petugas pada wilayahnya sendiri.',
        ];
    }
}