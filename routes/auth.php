<?php

use App\Http\Controllers\Auth\VerifyEmailCodeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'throttle:6,1'])->group(function (): void {
    Route::post('email/verify-code', [VerifyEmailCodeController::class, 'store'])
        ->name('verification.verify-code');
});
