<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TwoFaRequestController;

Route::group(['middleware' => 'accessibility:internal'], function () {
    Route::post('/start', [TwoFaRequestController::class, 'start'])->name('start');

    Route::get('/2fa-requests/next-action/{twoFaRequest}', [TwoFaRequestController::class, 'getNextAction'])->name('2fa_request.next_action');

    Route::prefix('/2fa-requests/{twoFaRequest}')->group(function () {
        Route::get('/', [TwoFaRequestController::class, 'show'])->name('2fa_request.show');
        Route::post('/mobile', [TwoFaRequestController::class, 'setMobile'])->name('2fa_request.set_mobile');
        Route::post('/client', [TwoFaRequestController::class, 'setClient'])->name('2fa_request.set_client');
        Route::post('/resend-code', [TwoFaRequestController::class, 'resendCode'])->name('2fa_request.resend_code');
        Route::post('/verify-code', [TwoFaRequestController::class, 'verifyCode'])->name('2fa_request.verify_code');
    });
});
