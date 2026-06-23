<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PlanController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // ── Public ──────────────────────────────
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/plans', [PlanController::class, 'index']);
    Route::get('/plans/{plan:slug}', [PlanController::class, 'show']);

    // ── Authenticated ───────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/profile/avatar', [AuthController::class, 'uploadAvatar']);


        Route::post('/checkout/{plan}', [CheckoutController::class, 'initiate']);
        Route::get('/payment/success', [CheckoutController::class, 'success']);
        Route::get('/payment/cancel', [CheckoutController::class, 'cancel']);
        Route::get('/my-orders', [DashboardController::class, 'orders']);
        Route::get('/my-payments', [DashboardController::class, 'payments']);
        Route::get('/my-subscriptions', [DashboardController::class, 'subscriptions']);
        Route::get('/my-active-subscription', [DashboardController::class, 'activeSubscription']);
    });
});