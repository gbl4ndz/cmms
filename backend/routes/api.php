<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MeterController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/login', [AuthController::class, 'login']);

// Authenticated
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Core resources
    Route::apiResource('locations',   LocationController::class);
    Route::apiResource('areas',       AreaController::class);
    Route::apiResource('contractors', ContractorController::class);
    Route::apiResource('categories',  CategoryController::class)->except('show');
    Route::apiResource('assets',      AssetController::class);
    Route::apiResource('parts',       PartController::class);
    Route::apiResource('users',       UserController::class);

    // Work Orders
    Route::apiResource('work-orders', WorkOrderController::class);
    Route::patch('/work-orders/{workOrder}/status',         [WorkOrderController::class, 'updateStatus']);
    Route::post('/work-orders/{workOrder}/comments',        [WorkOrderController::class, 'addComment']);
    Route::post('/work-orders/{workOrder}/parts',           [WorkOrderController::class, 'addPart']);
    Route::delete('/work-orders/{workOrder}/parts/{part}',  [WorkOrderController::class, 'removePart']);

    // Meters
    Route::apiResource('meters', MeterController::class)->except('update');
    Route::post('/meters/{meter}/readings',       [MeterController::class, 'addReading']);
    Route::post('/meters/{meter}/reset-baseline', [MeterController::class, 'resetBaseline']);

    // Media uploads
    Route::post('/assets/{asset}/media',          [MediaController::class, 'uploadToAsset']);
    Route::post('/work-orders/{workOrder}/media', [MediaController::class, 'uploadToWorkOrder']);
    Route::delete('/media/{media}',               [MediaController::class, 'destroy']);
});
