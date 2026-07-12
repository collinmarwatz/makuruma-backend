<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TruckController;
use App\Http\Controllers\Api\TruckDocumentController;
use App\Http\Controllers\Api\DocumentController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;

use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\StaffDocumentController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\DriverDocumentController;
// Public routes — no login required
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::get('roles', [RoleController::class, 'index']);
Route::apiResource('users', UserController::class);

// Protected routes — must be logged in (valid token required)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    Route::apiResource('staff', StaffController::class);
Route::get('staff/{staff}/documents', [StaffDocumentController::class, 'index']);
Route::post('staff/{staff}/documents', [StaffDocumentController::class, 'store']);

Route::apiResource('drivers', DriverController::class);
Route::get('drivers/{driver}/documents', [DriverDocumentController::class, 'index']);
Route::post('drivers/{driver}/documents', [DriverDocumentController::class, 'store']);

    Route::apiResource('trucks', TruckController::class);
    Route::get('trucks/{truck}/documents', [TruckDocumentController::class, 'index']);
    Route::post('trucks/{truck}/documents', [TruckDocumentController::class, 'store']);
    Route::apiResource('documents', DocumentController::class)->only(['show', 'update', 'destroy']);
});