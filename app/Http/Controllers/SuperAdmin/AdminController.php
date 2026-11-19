<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Admin as AdminProfile;
use App\Models\Kecamatan;
use App\Models\Puskesmas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $admins = AdminProfile::query()
            ->with(['user', 'puskesmas.kecamatan'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('superadmin.admins.index', compact('admins'));
    }

    public function create(): View
    {
        return view('superadmin.admins.create', [
            'kecamatans' => Kecamatan::orderBy('nama_kecamatan')->get(),
            'puskesmas' => Puskesmas::orderBy('nama_puskesmas')->get(),
            'puskesmasTerpakai' => AdminProfile::whereNotNull('puskesmas_id')
                ->pluck('puskesmas_id')
                ->map(fn ($id) => (int) $id)
                ->all(),
        ]);
    }
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\pL]+(?:[ ]+[\pL]+)*$/u',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'string', 'min:8'],
            'phone' => [
                'nullable',
                'regex:/^[0-9]+$/',
                'digits_between:10,13',
            ],
            'puskesmas_id' => [
                'required',
                'integer',
                Rule::exists('puskesmas', 'id'),
                Rule::unique('admin', 'puskesmas_id'),
            ],
        ], [
            'name.required' => 'Nama admin wajib diisi.',
            'name.regex' => 'Nama hanya boleh berisi huruf dan spasi.',
            'email.unique' => 'Email tersebut sudah terdaftar.',
            'puskesmas_id.required' => 'Puskesmas wajib dipilih.',
            'puskesmas_id.unique' => 'Puskesmas tersebut sudah memiliki Admin.',
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'status' => UserStatusEnum::Active->value,
            ]);

            $user->assignRole(RoleEnum::Admin->value);

            $user->admin()->create([
                'puskesmas_id' => $data['puskesmas_id'],
            ]);
        });

        return redirect()
            ->route('superadmin.admins.index')
            ->with('success', 'Admin Puskesmas berhasil ditambahkan.');
    }

    public function edit(AdminProfile $admin): View
    {
        return view('superadmin.admins.edit', [
            'admin' => $admin->load(['user', 'puskesmas.kecamatan']),
            'kecamatans' => Kecamatan::orderBy('nama_kecamatan')->get(),
            'puskesmas' => Puskesmas::orderBy('nama_puskesmas')->get(),
            'puskesmasTerpakai' => AdminProfile::whereNotNull('puskesmas_id')
                ->where('id', '!=', $admin->id)
                ->pluck('puskesmas_id')
                ->map(fn ($id) => (int) $id)
                ->all(),
        ]);
    }

    public function update(
        Request $request,
        AdminProfile $admin
    ): RedirectResponse {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\pL]+(?:[ ]+[\pL]+)*$/u',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($admin->user_id),
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'max:8',
            ],
            'phone' => [
                'nullable',
                'regex:/^[0-9]+$/',
                'digits_between:10,13',
            ],
            'puskesmas_id' => [
                'required',
                'integer',
                Rule::exists('puskesmas', 'id'),
                Rule::unique('admin', 'puskesmas_id')->ignore($admin->id),
            ],
        ], [
            'name.required' => 'Nama admin wajib diisi.',
            'name.regex' => 'Nama hanya boleh berisi huruf dan spasi.',
            'email.unique' => 'Email tersebut sudah terdaftar.',
            'password.min' => 'Password harus terdiri dari tepat 8 karakter.',
            'password.max' => 'Password harus terdiri dari tepat 8 karakter.',
            'puskesmas_id.required' => 'Puskesmas wajib dipilih.',
            'puskesmas_id.unique' => 'Puskesmas tersebut sudah memiliki Admin.',
        ]);

        DB::transaction(function () use ($admin, $data) {
            $admin->user->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);

            if (!empty($data['password'])) {
                $admin->user->password = $data['password'];
            }

            $admin->user->save();

            $admin->update([
                'puskesmas_id' => $data['puskesmas_id'],
            ]);
        });

        return redirect()
            ->route('superadmin.admins.index')
            ->with('success', 'Admin Puskesmas berhasil diperbarui.');
    }

    public function destroy(AdminProfile $admin): RedirectResponse
    {
        DB::transaction(function () use ($admin) {
            $admin->user->tokens()->delete();
            $admin->user->delete();
        });

        return redirect()
            ->route('superadmin.admins.index')
            ->with('success', 'Admin Puskesmas berhasil dihapus.');
    }
}
