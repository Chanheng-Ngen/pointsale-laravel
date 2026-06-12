<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/register', [RegisterController::class, 'register'])->name('register');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:6,1')->name('login');
    Route::delete('/logout', [LogoutController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed'])->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'sendVerificationEmail'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::post('/change-password', [ChangePasswordController::class, 'changePassword'])->middleware('auth:sanctum', 'throttle:6,1')->name('change.password');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'forgot'])->middleware('throttle:6,1')->name('forgot.password');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->middleware('throttle:6,1')->name('reset.password');
    Route::get('/me', [MeController::class, 'me'])->middleware('auth:sanctum')->name('me');
});

Route::middleware('auth:sanctum')->prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/', [CategoryController::class, 'store']);
    Route::get('/{category}', [CategoryController::class, 'show']);
    Route::put('/{category}', [CategoryController::class, 'update']);
    Route::delete('/{category}', [CategoryController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/', [ProductController::class, 'store']);
    Route::get('/{product}', [ProductController::class, 'show']);
    Route::put('/{product}', [ProductController::class, 'update']);
    Route::delete('/{product}', [ProductController::class, 'destroy']);
});


Route::apiResource('orders', OrderController::class);
Route::put('orders/{order}/status', [OrderController::class, 'updateStatus']);

Route::get('transactions/stats', [TransactionController::class, 'stats']);
Route::apiResource('transactions', TransactionController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('analytics', [AnalyticsController::class, 'index']);
    Route::get('dashboard/stats', [AnalyticsController::class, 'dashboard']);
});