<?php

use App\Http\Controllers\Admin\BidanController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DataPosyanduController;
use App\Http\Controllers\Admin\IbuController;
use App\Http\Controllers\Admin\KaderController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\LaporanPdfController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\WilayahKerjaController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Bidan\DashboardController as BidanDashboardController;
use App\Http\Controllers\Bidan\LaporanController as BidanLaporanController;
use App\Http\Controllers\DataAnakController;
use App\Http\Controllers\DataIbuController;
use App\Http\Controllers\Kader\DashboardController as KaderDashboardController;
use App\Http\Controllers\OrangTua\DashboardController as OrangTuaDashboardController;
use App\Http\Controllers\Panel\ComingSoonController;
use App\Http\Controllers\Panel\ProfileController;
use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = Auth::user();

    return $user && $user->primaryRole()
        ? redirect()->route($user->primaryRole()->dashboard())
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::get('modul/{module}', [ComingSoonController::class, 'show'])->name('panel.module');

    Route::middleware('role:super_admin')
        ->prefix('superadmin')
        ->name('superadmin.')
        ->group(function () {
            Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

            Route::get('admins', [AdminController::class, 'index'])->name('admins.index');
            Route::get('admins/create', [AdminController::class, 'create'])->name('admins.create');
            Route::post('admins', [AdminController::class, 'store'])->name('admins.store');
            Route::get('admins/{admin}/edit', [AdminController::class, 'edit'])->name('admins.edit');
            Route::put('admins/{admin}', [AdminController::class, 'update'])->name('admins.update');
            Route::delete('admins/{admin}', [AdminController::class, 'destroy'])->name('admins.destroy');

            Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
            Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
            Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');

            Route::get('permissions', [PermissionController::class, 'index'])
                ->middleware('can:userrole.view')->name('permissions.index');
            Route::get('permissions/role/{role}/edit', [PermissionController::class, 'editRole'])
                ->middleware('can:userrole.update')->name('permissions.role.edit');
            Route::put('permissions/role/{role}', [PermissionController::class, 'updateRole'])
                ->middleware('can:userrole.update')->name('permissions.role.update');
            Route::get('permissions/create', [PermissionController::class, 'create'])
                ->middleware('can:userpermission.create')->name('permissions.create');
            Route::post('permissions', [PermissionController::class, 'store'])
                ->middleware('can:userpermission.create')->name('permissions.store');
            Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])
                ->middleware('can:userpermission.update')->name('permissions.edit');
            Route::put('permissions/{permission}', [PermissionController::class, 'update'])
                ->middleware('can:userpermission.update')->name('permissions.update');
            Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])
                ->middleware('can:userpermission.delete')->name('permissions.destroy');

            Route::get('wilayah-kerja', [WilayahKerjaController::class, 'index'])
                ->middleware('can:wilayah.view')->name('wilayah-kerja.index');
            Route::get('wilayah-kerja/create', [WilayahKerjaController::class, 'create'])
                ->middleware('can:wilayah.create')->name('wilayah-kerja.create');
            Route::post('wilayah-kerja', [WilayahKerjaController::class, 'store'])
                ->middleware('can:wilayah.create')->name('wilayah-kerja.store');
            Route::get('wilayah-kerja/{wilayah}/edit', [WilayahKerjaController::class, 'edit'])
                ->middleware('can:wilayah.update')->name('wilayah-kerja.edit');
            Route::put('wilayah-kerja/{wilayah}', [WilayahKerjaController::class, 'update'])
                ->middleware('can:wilayah.update')->name('wilayah-kerja.update');
            Route::delete('wilayah-kerja/{wilayah}', [WilayahKerjaController::class, 'destroy'])
                ->middleware('can:wilayah.delete')->name('wilayah-kerja.destroy');
        });

    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

            Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
            Route::get('laporan/pdf', [LaporanPdfController::class, 'download'])->name('laporan.pdf');

            Route::get('data-ibu', [DataIbuController::class, 'index'])
                ->middleware('can:ibu.view')->name('data-ibu.index');
            Route::get('data-ibu/{ibu}', [DataIbuController::class, 'show'])
                ->middleware('can:ibu.view')->name('data-ibu.show');
            Route::get('data-ibu/{ibu}/edit', [DataIbuController::class, 'edit'])
                ->middleware('can:ibu.update')->name('data-ibu.edit');
            Route::put('data-ibu/{ibu}', [DataIbuController::class, 'update'])
                ->middleware('can:ibu.update')->name('data-ibu.update');

            Route::get('data-anak', [DataAnakController::class, 'index'])
                ->middleware('can:anak.view')->name('data-anak.index');
            Route::get('data-anak/create', [DataAnakController::class, 'create'])
                ->middleware('can:anak.create')->name('data-anak.create');
            Route::post('data-anak', [DataAnakController::class, 'store'])
                ->middleware('can:anak.create')->name('data-anak.store');
            Route::get('data-anak/{anak}', [DataAnakController::class, 'show'])
                ->middleware('can:anak.view')->name('data-anak.show');
            Route::get('data-anak/{anak}/edit', [DataAnakController::class, 'edit'])
                ->middleware('can:anak.update')->name('data-anak.edit');
            Route::put('data-anak/{anak}', [DataAnakController::class, 'update'])
                ->middleware('can:anak.update')->name('data-anak.update');

            Route::get('data-posyandu', [DataPosyanduController::class, 'index'])
                ->middleware('can:posyandu.view')->name('data-posyandu.index');
            Route::get('data-posyandu/rekap', [DataPosyanduController::class, 'rekap'])
                ->middleware('can:posyandu.view')->name('data-posyandu.rekap');
            Route::get('data-posyandu/{kegiatan}', [DataPosyanduController::class, 'show'])
                ->middleware('can:posyandu.view')->name('data-posyandu.show');

            Route::get('bidan', [BidanController::class, 'index'])
                ->middleware('can:user.view')->name('bidan.index');
            Route::get('bidan/create', [BidanController::class, 'create'])
                ->middleware('can:user.create')->name('bidan.create');
            Route::post('bidan', [BidanController::class, 'store'])
                ->middleware('can:user.create')->name('bidan.store');
            Route::get('bidan/{bidan}/edit', [BidanController::class, 'edit'])
                ->middleware('can:user.update')->name('bidan.edit');
            Route::put('bidan/{bidan}', [BidanController::class, 'update'])
                ->middleware('can:user.update')->name('bidan.update');
            Route::delete('bidan/{bidan}', [BidanController::class, 'destroy'])
                ->middleware('can:user.delete')->name('bidan.destroy');

            Route::get('kader', [KaderController::class, 'index'])
                ->middleware('can:user.view')->name('kader.index');
            Route::get('kader/create', [KaderController::class, 'create'])
                ->middleware('can:user.create')->name('kader.create');
            Route::post('kader', [KaderController::class, 'store'])
                ->middleware('can:user.create')->name('kader.store');
            Route::get('kader/{kader}/edit', [KaderController::class, 'edit'])
                ->middleware('can:user.update')->name('kader.edit');
            Route::put('kader/{kader}', [KaderController::class, 'update'])
                ->middleware('can:user.update')->name('kader.update');
            Route::delete('kader/{kader}', [KaderController::class, 'destroy'])
                ->middleware('can:user.delete')->name('kader.destroy');

            Route::get('ibu', [IbuController::class, 'index'])
                ->middleware('can:user.view')->name('ibu.index');
            Route::get('ibu/create', [IbuController::class, 'create'])
                ->middleware('can:user.create')->name('ibu.create');
            Route::post('ibu', [IbuController::class, 'store'])
                ->middleware('can:user.create')->name('ibu.store');
            Route::get('ibu/{ibu}/edit', [IbuController::class, 'edit'])
                ->middleware('can:user.update')->name('ibu.edit');
            Route::put('ibu/{ibu}', [IbuController::class, 'update'])
                ->middleware('can:user.update')->name('ibu.update');
            Route::patch('ibu/{ibu}/toggle-status', [IbuController::class, 'toggleStatus'])
                ->middleware('can:user.activate')->name('ibu.toggle-status');
        });

    Route::middleware('role:bidan')
        ->prefix('bidan')
        ->name('bidan.')
        ->group(function () {
            Route::get('/', [BidanDashboardController::class, 'index'])->name('dashboard');
            Route::get('laporan', [BidanLaporanController::class, 'index'])->name('laporan.index');
            Route::get('data-ibu', [DataIbuController::class, 'index'])->name('data-ibu.index');
            Route::get('data-ibu/{ibu}', [DataIbuController::class, 'show'])->name('data-ibu.show');
            Route::get('data-anak', [DataAnakController::class, 'index'])->name('data-anak.index');
            Route::get('data-anak/{anak}', [DataAnakController::class, 'show'])->name('data-anak.show');
            Route::get('data-posyandu', [DataPosyanduController::class, 'index'])->name('data-posyandu.index');
            Route::get('data-posyandu/rekap', [DataPosyanduController::class, 'rekap'])->name('data-posyandu.rekap');
            Route::get('data-posyandu/{kegiatan}', [DataPosyanduController::class, 'show'])->name('data-posyandu.show');
        });

    Route::middleware('role:kader')
        ->prefix('kader')
        ->name('kader.')
        ->group(function () {
            Route::get('/', [KaderDashboardController::class, 'index'])->name('dashboard');
            Route::get('data-ibu', [DataIbuController::class, 'index'])->name('data-ibu.index');
            Route::get('data-ibu/{ibu}', [DataIbuController::class, 'show'])->name('data-ibu.show');
            Route::get('data-anak', [DataAnakController::class, 'index'])->name('data-anak.index');
            Route::get('data-anak/{anak}', [DataAnakController::class, 'show'])->name('data-anak.show');
            Route::get('data-posyandu', [DataPosyanduController::class, 'index'])->name('data-posyandu.index');
            Route::get('data-posyandu/rekap', [DataPosyanduController::class, 'rekap'])->name('data-posyandu.rekap');
            Route::get('data-posyandu/{kegiatan}', [DataPosyanduController::class, 'show'])->name('data-posyandu.show');
        });

    Route::middleware('role:ibu')
        ->prefix('ibu')
        ->name('ibu.')
        ->group(function () {
            Route::get('/', [OrangTuaDashboardController::class, 'index'])->name('dashboard');
            Route::get('data-ibu', [DataIbuController::class, 'index'])->name('data-ibu.index');
            Route::get('data-ibu/{ibu}', [DataIbuController::class, 'show'])->name('data-ibu.show');
            Route::get('data-anak', [DataAnakController::class, 'index'])->name('data-anak.index');
            Route::get('data-anak/{anak}', [DataAnakController::class, 'show'])->name('data-anak.show');
        });
});