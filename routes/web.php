<?php


use App\Http\Controllers\OrangTuaDashboardController as IbuDashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\BidanController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\IbuController;
use App\Http\Controllers\Admin\KaderController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Bidan\DashboardController as BidanDashboardController;
use App\Http\Controllers\Kader\DashboardController as KaderDashboardController;
use App\Http\Controllers\OrangTua\DashboardController as OrangTuaDashboardController;
use App\Http\Controllers\Panel\ComingSoonController;
use App\Http\Controllers\Panel\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
// Halaman awal: arahkan pengguna sesuai role
Route::get('/', function () {
    $user = Auth::user();

    return $user && $user->primaryRole()
        ? redirect()->route($user->primaryRole()->dashboard())
        : redirect()->route('login');
});

// Login
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// Logout
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Semua route yang membutuhkan login
Route::middleware('auth')->group(function () {

    // Profil pengguna
    Route::get('profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    // Modul
    Route::get('modul/{module}', [ComingSoonController::class, 'show'])
        ->name('panel.module');

    // =========================
    // DASHBOARD ADMIN
    // =========================
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/', [AdminDashboardController::class, 'index'])
                ->name('dashboard');

            Route::get('roles', [RoleController::class, 'index'])
                ->name('roles.index');

            Route::get('permissions', [PermissionController::class, 'index'])
                ->name('permissions.index');

            Route::put('permissions', [PermissionController::class, 'update'])
                ->name('permissions.update');
        });

    // =========================
    // KELOLA DATA (PERMISSION)
    // =========================
    Route::prefix('admin')->name('admin.')->group(function () {

        // Kelola Bidan
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

        // Kelola Kader
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

        // Kelola Ibu
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

    
    // DASHBOARD BIDAN
   
    Route::middleware('role:bidan')
        ->prefix('bidan')
        ->name('bidan.')
        ->group(function () {
            Route::get('/', [BidanDashboardController::class, 'index'])
                ->name('dashboard');
        });


    // DASHBOARD KADER
        
    Route::middleware('role:kader')
        ->prefix('kader')
        ->name('kader.')
        ->group(function () {
            Route::get('/', [KaderDashboardController::class, 'index'])
                ->name('dashboard');
        });

    
    // DASHBOARD IBU
    
    Route::middleware('role:ibu')
        ->prefix('ibu')
        ->name('ibu.')
        ->group(function () {
            Route::get('/', [OrangTuaDashboardController::class, 'index'])
                ->name('dashboard');
        });

});