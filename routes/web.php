<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Bidan\DashboardController as BidanDashboardController;
use App\Http\Controllers\Kader\DashboardController as KaderDashboardController;
use App\Http\Controllers\OrangTua\DashboardController as OrangTuaDashboardController;
use App\Http\Controllers\Panel\ComingSoonController;
use App\Http\Controllers\Panel\KelolaOrangTuaController;
use App\Http\Controllers\Panel\ProfileController;
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
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('modul/{module}', [ComingSoonController::class, 'show'])->name('panel.module');

    Route::middleware('role:admin|bidan|kader')
        ->prefix('kelola-orang-tua')
        ->name('panel.kelola-orang-tua.')
        ->group(function () {
            Route::get('/', [KelolaOrangTuaController::class, 'index'])->name('index');
            Route::get('create', [KelolaOrangTuaController::class, 'create'])->name('create');
            Route::post('/', [KelolaOrangTuaController::class, 'store'])->name('store');
            Route::get('{orangTua}', [KelolaOrangTuaController::class, 'show'])->name('show');
            Route::get('{orangTua}/edit', [KelolaOrangTuaController::class, 'edit'])->name('edit');
            Route::put('{orangTua}', [KelolaOrangTuaController::class, 'update'])->name('update');
            Route::delete('{orangTua}', [KelolaOrangTuaController::class, 'destroy'])->name('destroy');
        });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::patch('users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
        Route::resource('users', UserController::class);
    });

    Route::middleware('role:bidan')->prefix('bidan')->name('bidan.')->group(function () {
        Route::get('/', [BidanDashboardController::class, 'index'])->name('dashboard');
    });

    Route::middleware('role:kader')->prefix('kader')->name('kader.')->group(function () {
        Route::get('/', [KaderDashboardController::class, 'index'])->name('dashboard');
    });

    Route::middleware('role:orang_tua')->prefix('orang-tua')->name('orang-tua.')->group(function () {
        Route::get('/', [OrangTuaDashboardController::class, 'index'])->name('dashboard');
    });
});
