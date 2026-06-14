<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v2\Customer\AuthController;
use App\Http\Controllers\v2\User\UserController;
use App\Http\Controllers\v2\User\TransactionController;
use App\Http\Controllers\v2\User\PasscodeController;
use App\Http\Controllers\v2\Setting\SliderController;
use App\Http\Controllers\v2\Spin\SpinController;
use App\Http\Controllers\v2\Transfer\TransferController;
use App\Http\Controllers\v2\Announcement\AnnouncementController;

Route::prefix('auth')->group(function () {
    Route::post('request-otp', [AuthController::class, 'requestOtp'])
        ->middleware('throttle:100,1')
        ->name('customer.auth.request-otp');

    Route::post('verify-otp', [AuthController::class, 'verifyOtp'])
        ->middleware('throttle:100,1')
        ->name('customer.auth.verify-otp');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('user/profile', [UserController::class, 'show']);
    Route::post('user/save-fcm-token', [UserController::class, 'saveFcmToken']);
    Route::get('user/discountlist', [UserController::class, 'discountList']);

    Route::get('user/transactions', [TransactionController::class, 'index']);

    Route::post('user/passcode/create', [PasscodeController::class, 'create']);
    Route::post('user/passcode/verify', [PasscodeController::class, 'verify']);
    Route::post('user/passcode/forget', [PasscodeController::class, 'forget']);
    Route::post('user/passcode/reset', [PasscodeController::class, 'reset']);

    Route::get('slider', [SliderController::class, 'index']);
    Route::get('user/slider', [SliderController::class, 'index']);

    Route::post('spin/getdiscount', [SpinController::class, 'getDiscount']);

    Route::get('transfer/receiver-check', [TransferController::class, 'checkReceiver']);
    Route::post('transfer/spin', [TransferController::class, 'transferSpin']);
    Route::post('transfer/discount', [TransferController::class, 'transferDiscount']);
    Route::get(
    '/announcement',
        [AnnouncementController::class, 'index']
    );
});