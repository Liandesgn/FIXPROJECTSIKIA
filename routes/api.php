<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WilayahKerjaController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
    Route::put('profile/password', [ProfileController::class, 'updatePassword']);

    Route::get('roles', [RoleController::class, 'index'])->middleware('permission:user.assign-role');
    Route::get('wilayah-kerja', [WilayahKerjaController::class, 'index'])->middleware('permission:user.view');

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware('permission:user.view');
        Route::post('/', [UserController::class, 'store'])->middleware('permission:user.create');
        Route::get('{user}', [UserController::class, 'show'])->middleware('permission:user.view');
        Route::put('{user}', [UserController::class, 'update'])->middleware('permission:user.update');
        Route::delete('{user}', [UserController::class, 'destroy'])->middleware('permission:user.delete');
        Route::patch('{user}/activate', [UserController::class, 'activate'])->middleware('permission:user.activate');
        Route::patch('{user}/deactivate', [UserController::class, 'deactivate'])->middleware('permission:user.activate');
    });
});
