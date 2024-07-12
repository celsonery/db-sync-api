<?php

use App\Http\Controllers\DatabaseController;
use Illuminate\Support\Facades\Route;



//Route::middleware('auth:sanctum')->group(function () {
    Route::get('/databases', [DatabaseController::class, 'index'])->name('database.index');
    Route::post('/databases', [DatabaseController::class, 'verify'])->name('database.verify');
//});
