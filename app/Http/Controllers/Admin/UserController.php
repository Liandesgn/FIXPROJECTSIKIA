<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
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
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => RoleEnum::manageable(),
            'statuses' => UserStatusEnum::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => RoleEnum::manageable(),
            'statuses' => UserStatusEnum::cases(),
            'wilayahs' => WilayahKerja::orderBy('nama')->get(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
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

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    public function show(User $user): View
    {
        $user->load(['roles', 'wilayahKerja']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $user->load(['roles', 'wilayahKerja']);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => RoleEnum::manageable(),
            'statuses' => UserStatusEnum::cases(),
            'wilayahs' => WilayahKerja::orderBy('nama')->get(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $user->fill(Arr::only($data, ['name', 'email', 'phone', 'status', 'wilayah_kerja_id']));

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        if (! empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->tokens()->delete();
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    public function activate(User $user): RedirectResponse
    {
        $user->update(['status' => UserStatusEnum::Active->value]);

        return back()->with('success', 'Akun berhasil diaktifkan.');
    }

    public function deactivate(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['status' => UserStatusEnum::Inactive->value]);
        $user->tokens()->delete();

        return back()->with('success', 'Akun berhasil dinonaktifkan.');
    }
}
