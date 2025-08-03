<?php


use App\Http\Controllers\Api\V1\AccessController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OrganizationController;
use Illuminate\Support\Facades\Route;

Route::get('health', function () {
    return response()->json(['status' => 'ok']);
})->name('health');


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

    Route::apiResource('organizations', OrganizationController::class);

    Route::name('access.')->prefix('access')->group(function () {
        Route::get('list', [AccessController::class, 'list'])->name('list');
        Route::post('invite', [AccessController::class, 'invite'])->name('invite');
        Route::post('accept', [AccessController::class, 'accept'])->name('accept');
        Route::post('reject', [AccessController::class, 'reject'])->name('reject');
        Route::post('revoke', [AccessController::class, 'revoke'])->name('revoke');
    });

});
