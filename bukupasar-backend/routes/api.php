<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\RentalTypeController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now()->toISOString(),
        'service' => 'Bukupasar API',
    ]);
});

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{jenis}', [CategoryController::class, 'byJenis'])
        ->whereIn('jenis', ['pemasukan', 'pengeluaran']);

    Route::get('/rental-types', [RentalTypeController::class, 'index']);

    Route::get('/tenants/search/{query}', [TenantController::class, 'search']);
    Route::apiResource('tenants', TenantController::class);

    Route::apiResource('transactions', TransactionController::class);
    Route::apiResource('payments', PaymentController::class)->only(['index', 'store']);

    Route::get('/reports/daily', [ReportController::class, 'daily']);
    Route::get('/reports/summary', [ReportController::class, 'summary']);
    Route::get('/reports/cashbook', [ReportController::class, 'cashbook']);
    Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss']);
});
