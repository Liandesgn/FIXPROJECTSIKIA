<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return RoleResource::collection(
            Role::with('permissions')->orderBy('name')->get()
        );
    }
}
