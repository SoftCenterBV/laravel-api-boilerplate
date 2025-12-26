<?php


use App\Http\Controllers\Api\V1\AccessController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OrganizationController;
use App\Http\Controllers\Api\V1\OrganizationUserController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::get('health', function () {
    return response()->json(['status' => 'ok']);
})->name('health');


Route::name('auth.')->prefix('auth')->middleware(['throttle:basic-login'])->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('verify-mfa', [AuthController::class, 'verifyMfa'])->name('mfa.verify');
    Route::post('register', [AuthController::class, 'register'])->name('register');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::name('auth.')->prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('setup-mfa', [AuthController::class, 'initMfaSetup'])->name('mfa.setup');
        Route::post('setup-mfa', [AuthController::class, 'verifyMfaSetup'])->name('mfa.setup.verify');
    });

    Route::name('user.')->prefix('users')->group(function (){
        Route::get('me',[UserController::class, 'me'])->name('me');
        Route::patch('{user}',[UserController::class, 'update'])->name('update');
        Route::delete('{user}',[UserController::class, 'destroy'])->name('destroy');
        Route::get('export-data',[UserController::class, 'exportData'])->name('export-data');

    });
//    Route::apiResource('organizations', OrganizationController::class);

    Route::name('organization.')->prefix('organization')->group(function () {
        Route::get('/',[OrganizationController::class, 'index'])->name('index');
        Route::prefix('{organization}')->group(function () {
            Route::get('/',[OrganizationController::class, 'show'])->name('show');
            Route::patch('/',[OrganizationController::class, 'update'])->name('update');
            Route::delete('/',[OrganizationController::class, 'destroy'])->name('destroy');
            Route::name('user.')->prefix('user')->group(function () {
                Route::get('list', [OrganizationUserController::class, 'list'])->name('list');
                Route::get('pending-invites', [OrganizationUserController::class, 'pendingInvites'])->name('pending-invites');
                Route::post('delete', [OrganizationUserController::class, 'delete'])->name('delete');
            });
        });
    });

    Route::name('access.')->prefix('access')->group(function () {
        Route::get('list', [AccessController::class, 'list'])->name('list');
        Route::post('invite', [AccessController::class, 'invite'])->name('invite');
        Route::post('accept', [AccessController::class, 'accept'])->name('accept');
        Route::post('reject', [AccessController::class, 'reject'])->name('reject');
        Route::post('revoke', [AccessController::class, 'revoke'])->name('revoke');
    });

});
