<?php


use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OrganizationController;
use Illuminate\Support\Facades\Route;

Route::name('auth.')->prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
 });

Route::middleware(['auth:sanctum'])->group(function () {
    Route::name('auth.')->prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
    Route::apiResource('organizations', OrganizationController::class);
});
