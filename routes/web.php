<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Portal\Auth\PortalAuthController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\ProfileController;
use App\Http\Controllers\Portal\SliderController;
use App\Http\Controllers\Portal\SpinRewardController;
use App\Http\Controllers\Portal\CustomerController;

Route::get('/', function () {
    return redirect()->route('portal.login');
});

Route::middleware('guest')->group(function () {
    Route::get('/portal/login', [PortalAuthController::class, 'showLoginForm'])
        ->name('portal.login');

    Route::post('/portal/login', [PortalAuthController::class, 'login'])
        ->name('portal.login.submit');
});

Route::middleware('auth')->prefix('portal')->name('portal.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::resource('spin-rewards', SpinRewardController::class)
    ->except(['show']);

    Route::resource('sliders', SliderController::class)
        ->except(['show']);

    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{user}/wallet', [CustomerController::class, 'wallet'])->name('customers.wallet');
    Route::post('/customers/{user}/add-spin', [CustomerController::class, 'addSpin'])->name('customers.add-spin');
    Route::post('/customers/{user}/add-discount', [CustomerController::class, 'addDiscount'])->name('customers.add-discount');



    Route::post('/logout', [PortalAuthController::class, 'logout'])
        ->name('logout');
});