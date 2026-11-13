<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WilayahKerjaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_wilayah' => $this->nama_wilayah,
            'kelurahan' => $this->kelurahan,
            'kecamatan' => $this->kecamatan,
            'kabupaten' => $this->kabupaten,
            'provinsi' => $this->provinsi,
            'admin' => $this->whenLoaded('admins', fn () => $this->admins->first()?->user?->name),
            'bidan' => $this->whenLoaded('bidan', fn () => $this->bidan?->name),
            'jumlah_kader' => $this->whenLoaded('kaders', fn () => $this->kaders->count()),
        ];
    }
}