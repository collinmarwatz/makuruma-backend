<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;

use App\Http\Controllers\Api\TruckController;
use App\Http\Controllers\Api\TruckDocumentController;
use App\Http\Controllers\Api\DocumentController;

use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\StaffDocumentController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\DriverDocumentController;

use App\Http\Controllers\Api\TrailerController;
use App\Http\Controllers\Api\TrailerDocumentController;

use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\VendorController;

use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\TripLegController;
use App\Http\Controllers\Api\ConvoyController;

use App\Http\Controllers\Api\CheckpointController;
use App\Http\Controllers\Api\TrackingController;

use App\Http\Controllers\Api\ExpenseOrderController;

// Public routes — no login required
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes — must be logged in (valid token required)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    Route::get('roles', [RoleController::class, 'index']);
    Route::apiResource('users', UserController::class);

    Route::apiResource('trucks', TruckController::class);
    Route::get('trucks/{truck}/documents', [TruckDocumentController::class, 'index']);
    Route::post('trucks/{truck}/documents', [TruckDocumentController::class, 'store']);
    Route::apiResource('documents', DocumentController::class)->only(['show', 'update', 'destroy']);

    Route::apiResource('staff', StaffController::class);
    Route::get('staff/{staff}/documents', [StaffDocumentController::class, 'index']);
    Route::post('staff/{staff}/documents', [StaffDocumentController::class, 'store']);

    Route::apiResource('drivers', DriverController::class);
    Route::get('drivers/{driver}/documents', [DriverDocumentController::class, 'index']);
    Route::post('drivers/{driver}/documents', [DriverDocumentController::class, 'store']);

    Route::apiResource('trailers', TrailerController::class);
    Route::get('trailers/{trailer}/documents', [TrailerDocumentController::class, 'index']);
    Route::post('trailers/{trailer}/documents', [TrailerDocumentController::class, 'store']);

    Route::apiResource('clients', ClientController::class);
    Route::apiResource('vendors', VendorController::class);

    Route::post('trips/find-by-number', [TripLegController::class, 'findByTripNumber']);
    Route::get('trips/{trip}/download', [TripController::class, 'download']);
    Route::apiResource('trips', TripController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::post('trips/{trip}/legs', [TripLegController::class, 'store']);
    Route::put('trip-legs/{leg}', [TripLegController::class, 'update']);

    Route::apiResource('convoys', ConvoyController::class)->only(['index', 'store']);

    Route::apiResource('checkpoints', CheckpointController::class)->only(['index', 'store']);
    Route::get('tracking', [TrackingController::class, 'index']);
    Route::get('tracking/{bookingTruck}', [TrackingController::class, 'show']);
    Route::put('tracking/{bookingTruck}/status', [TrackingController::class, 'updateStatus']);
    Route::post('tracking/{bookingTruck}/milestones', [TrackingController::class, 'upsertMilestone']);
    Route::get('tracking/{bookingTruck}/download', [TrackingController::class, 'download']);

    Route::apiResource('expense-orders', ExpenseOrderController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::post('expense-orders/{expenseOrder}/approve', [ExpenseOrderController::class, 'approve']);
    Route::post('expense-orders/{expenseOrder}/reject', [ExpenseOrderController::class, 'reject']);
    Route::post('expense-orders/{expenseOrder}/mark-paid', [ExpenseOrderController::class, 'markPaid']);
    Route::get('expense-orders/{expenseOrder}/download', [ExpenseOrderController::class, 'download']);
});