<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Routes (Requires Bearer Token)
Route::middleware('auth:sanctum')->group(function () {
    // Logout & Universal User Profile
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'userProfile']);
    Route::get('/profile', [AuthController::class, 'userProfile']);

    // Admin-Only Routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/students', [StudentController::class, 'index']);
        Route::get('/admin/students/{id}', [StudentController::class, 'show']);
    });

    // Student-Only Routes
    Route::middleware('role:student')->group(function () {
        Route::get('/student/profile', [StudentController::class, 'myProfile']);
    });
});
