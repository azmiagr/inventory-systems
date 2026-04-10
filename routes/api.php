<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/ping', fn(Request $r) => ApiResponse::success([
            'name' => $r->user()->name,
        ], 'Hello Admin!'));
    });

    Route::middleware('role:admin,staff')->group(function () {
        Route::get('/staff/ping', fn(Request $r) => ApiResponse::success([
            'name' => $r->user()->name,
            'role' => $r->user()->role->name,
        ], 'Hello ' . $r->user()->role->name . '!'));
    });
});