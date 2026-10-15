<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'role' => $this->primaryRole()?->value,
            'role_label' => $this->primaryRole()?->label(),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'wilayah_kerja' => $this->whenLoaded('wilayahKerja', fn () => new WilayahKerjaResource($this->wilayahKerja)),
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
