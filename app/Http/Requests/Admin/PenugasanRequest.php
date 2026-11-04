<?php

namespace App\Http\Requests\Admin;

use App\Models\Bidan;
use App\Models\Kader;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PenugasanRequest extends FormRequest
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
            'wilayah_kerja_id' => ['required', 'integer', Rule::exists('wilayah_kerja', 'id')],
            'bidan_id' => ['required', 'integer', Rule::exists('bidan', 'user_id')],
           'kader_ids' => ['nullable', 'array', 'max:3'],
            'kader_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('kader', 'user_id'),
            ],
                    ];
    }

    /**
     * Bidan/kader yang sudah bertugas di wilayah lain tidak boleh dipilih.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $wilayahId = (int) $this->input('wilayah_kerja_id');

            $asalBidan = Bidan::where('user_id', $this->input('bidan_id'))->value('wilayah_kerja_id');
            if ($asalBidan && (int) $asalBidan !== $wilayahId) {
                $validator->errors()->add('bidan_id', 'Bidan sudah bertugas di wilayah lain.');
            }

            foreach ((array) $this->input('kader_ids', []) as $kaderId) {
                $asalKader = Kader::where('user_id', $kaderId)->value('wilayah_kerja_id');
                if ($asalKader && (int) $asalKader !== $wilayahId) {
                    $validator->errors()->add('kader_ids', 'Kader sudah bertugas di wilayah lain.');
                    break;
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
        'wilayah_kerja_id.required' => 'Wilayah wajib dipilih.',
        'wilayah_kerja_id.exists' => 'Wilayah tidak ditemukan.',
        'bidan_id.required' => 'Bidan penanggung jawab wajib dipilih.',
        'bidan_id.exists' => 'Akun bidan penanggung jawab tidak ditemukan.',
        'kader_ids.max' => 'Maksimal hanya boleh memilih 3 kader dalam satu wilayah.',
        'kader_ids.*.distinct' => 'Kader yang sama tidak boleh dipilih lebih dari satu kali.',
        'kader_ids.*.exists' => 'Akun kader wilayah tidak ditemukan.',
        ];
    }
}