<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

// Satu-satunya endpoint tanpa token
Route::post('/login', [AuthController::class, 'login']);

// Semua yang lain wajib token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('books', BookController::class);
});
