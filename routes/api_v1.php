<?php


use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::name('auth.')->prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('verify-mfa', [AuthController::class, 'verifyMfa'])->name('mfa.verify');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::name('auth.')->prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('setup-mfa', [AuthController::class, 'initMfaSetup'])->name('mfa.setup');
        Route::post('setup-mfa', [AuthController::class, 'verifyMfaSetup'])->name('mfa.setup.verify');
    });
});
