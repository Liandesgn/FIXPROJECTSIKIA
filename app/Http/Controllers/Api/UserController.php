<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->with(['roles', 'wilayahKerja'])
            ->when($request->filled('role'), fn ($query) => $query->role((string) $request->string('role')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', (string) $request->string('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(fn ($query) => $query
                    ->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search));
            })
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'] ?? UserStatusEnum::Active->value,
            'wilayah_kerja_id' => $data['wilayah_kerja_id'] ?? null,
        ]);

        $user->assignRole($data['role']);

        return response()->json([
            'message' => 'User berhasil dibuat.',
            'data' => (new UserResource($user->load('roles', 'wilayahKerja')))->resolve(),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json([
            'data' => (new UserResource($user->load('roles', 'wilayahKerja')))->resolve(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        $user->fill(Arr::only($data, ['name', 'email', 'phone', 'status', 'wilayah_kerja_id']));

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        if (! empty($data['role'])) {
            $this->authorize('assignRole', $user);
            $user->syncRoles([$data['role']]);
        }

        return response()->json([
            'message' => 'User berhasil diperbarui.',
            'data' => (new UserResource($user->load('roles', 'wilayahKerja')))->resolve(),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus.',
        ]);
    }

    public function activate(User $user): JsonResponse
    {
        $this->authorize('activate', $user);

        $user->update(['status' => UserStatusEnum::Active->value]);

        return response()->json([
            'message' => 'Akun berhasil diaktifkan.',
            'data' => (new UserResource($user->load('roles', 'wilayahKerja')))->resolve(),
        ]);
    }

    public function deactivate(User $user): JsonResponse
    {
        $this->authorize('deactivate', $user);

        $user->update(['status' => UserStatusEnum::Inactive->value]);
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Akun berhasil dinonaktifkan.',
            'data' => (new UserResource($user->load('roles', 'wilayahKerja')))->resolve(),
        ]);
    }
}
