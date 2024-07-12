<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DatabaseController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('/user', [AuthController::class, 'user'])->name('auth.user');

        Route::get('/databases', [DatabaseController::class, 'index'])->name('database.index');
        Route::post('/databases', [DatabaseController::class, 'sync'])->name('database.sync');
    });
});

Route::get('/', function () {
    return response()->json(['message' => "Api funcionando ok"], 200);
});
