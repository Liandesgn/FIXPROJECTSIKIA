<?php

namespace App\Http\Controllers\Api;

use App\Enums\RoleEnum;
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
            ->when($request->user()->isAdmin(), function ($query) use ($request) {
                $wilayahId = $request->user()->loadMissing('admin')->managedWilayahId();
                abort_unless($wilayahId, 403, 'Akun Admin belum memiliki wilayah kerja.');
                $query->where('wilayah_kerja_id', $wilayahId);
            })
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

        if ($request->user()->isAdmin()) {
            $wilayahId = $request->user()->loadMissing('admin')->managedWilayahId();
            abort_unless($wilayahId, 403, 'Akun Admin belum memiliki wilayah kerja.');
            $data['wilayah_kerja_id'] = $wilayahId;
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'] ?? UserStatusEnum::Active->value,
            'wilayah_kerja_id' => $data['wilayah_kerja_id'] ?? null,
        ]);

        $user->assignRole($data['role']);

        if (in_array($data['role'], [RoleEnum::Bidan->value, RoleEnum::Kader->value], true)
            && ! empty($data['wilayah_kerja_id'])) {
            $user->assignWilayah((int) $data['wilayah_kerja_id']);
        }

        return response()->json([
            'message' => 'User berhasil dibuat.',
            'data' => (new UserResource($user->load('roles', 'wilayahKerja')))->resolve(),
        ], 201);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        $this->authorize('view', $user);
        $this->ensureManaged($request->user(), $user);

        return response()->json([
            'data' => (new UserResource($user->load('roles', 'wilayahKerja')))->resolve(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);
        $this->ensureManaged($request->user(), $user);

        $data = $request->validated();

        if ($request->user()->isAdmin()) {
            $data['wilayah_kerja_id'] = $request->user()->loadMissing('admin')->managedWilayahId();
        }

        $user->fill(Arr::only($data, ['name', 'email', 'phone', 'status', 'wilayah_kerja_id']));

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        if (! empty($data['role'])) {
            $this->authorize('assignRole', $user);
            $user->syncRoles([$data['role']]);
            $user->unsetRelation('roles');
        }

        $role = $data['role'] ?? $user->primaryRole()?->value;

        if (in_array($role, [RoleEnum::Bidan->value, RoleEnum::Kader->value], true)
            && ! empty($data['wilayah_kerja_id'])) {
            $user->assignWilayah((int) $data['wilayah_kerja_id']);
        }

        return response()->json([
            'message' => 'User berhasil diperbarui.',
            'data' => (new UserResource($user->load('roles', 'wilayahKerja')))->resolve(),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        $this->ensureManaged($request->user(), $user);

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus.',
        ]);
    }

    public function activate(Request $request, User $user): JsonResponse
    {
        $this->authorize('activate', $user);
        $this->ensureManaged($request->user(), $user);

        $user->update(['status' => UserStatusEnum::Active->value]);

        return response()->json([
            'message' => 'Akun berhasil diaktifkan.',
            'data' => (new UserResource($user->load('roles', 'wilayahKerja')))->resolve(),
        ]);
    }

    public function deactivate(Request $request, User $user): JsonResponse
    {
        $this->authorize('deactivate', $user);
        $this->ensureManaged($request->user(), $user);

        $user->update(['status' => UserStatusEnum::Inactive->value]);
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Akun berhasil dinonaktifkan.',
            'data' => (new UserResource($user->load('roles', 'wilayahKerja')))->resolve(),
        ]);
    }

    private function ensureManaged(User $actor, User $target): void
    {
        if (! $actor->isAdmin()) {
            return;
        }

        $wilayahId = $actor->loadMissing('admin')->managedWilayahId();

        abort_unless(
            $wilayahId && (int) $target->wilayah_kerja_id === (int) $wilayahId,
            403,
            'Akses ditolak.'
        );
    }
}
