<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Portal\Auth\PortalAuthController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\ProfileController;
use App\Http\Controllers\Portal\SliderController;
use App\Http\Controllers\Portal\SpinRewardController;
use App\Http\Controllers\Portal\CustomerController;
use App\Http\Controllers\Portal\DiscountController;
use App\Http\Controllers\Portal\SpinCampaignController;
use App\Http\Controllers\Portal\SpinSubCampaignController;

Route::get('/', function () {
    return redirect()->route('portal.login');
});

/*
|--------------------------------------------------------------------------
| Guest Portal Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get(
        '/portal/login',
        [PortalAuthController::class, 'showLoginForm']
    )->name('portal.login');

    Route::post(
        '/portal/login',
        [PortalAuthController::class, 'login']
    )->name('portal.login.submit');
});

/*
|--------------------------------------------------------------------------
| Authenticated Portal Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('portal')
    ->name('portal.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dashboard',
            [DashboardController::class, 'index']
        )->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/profile',
            [ProfileController::class, 'edit']
        )->name('profile.edit');

        Route::put(
            '/profile',
            [ProfileController::class, 'update']
        )->name('profile.update');

        /*
        |--------------------------------------------------------------------------
        | Spin Rewards
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'spin-rewards',
            SpinRewardController::class
        )->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | Sliders
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'sliders',
            SliderController::class
        )->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | Discounts
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'discounts',
            DiscountController::class
        )->except(['show']);

        Route::patch(
            'discounts/{discount}/toggle-status',
            [DiscountController::class, 'toggleStatus']
        )->name('discounts.toggle-status');

        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/customers',
            [CustomerController::class, 'index']
        )->name('customers.index');

        Route::get(
            '/customers/{user}/wallet',
            [CustomerController::class, 'wallet']
        )->name('customers.wallet');

        Route::post(
            '/customers/{user}/add-spin',
            [CustomerController::class, 'addSpin']
        )->name('customers.add-spin');

        Route::post(
            '/customers/{user}/add-discount',
            [CustomerController::class, 'addDiscount']
        )->name('customers.add-discount');

        /*
        |--------------------------------------------------------------------------
        | Main Spin Campaigns
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'spin-campaigns',
            SpinCampaignController::class
        )->parameters([
            'spin-campaigns' => 'spinCampaign',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Subcampaigns
        |--------------------------------------------------------------------------
        */

        Route::prefix('spin-campaigns/{campaign}/sub-campaigns')
            ->name('spin-campaigns.sub-campaigns.')
            ->group(function () {

                Route::get(
                    '/',
                    [SpinSubCampaignController::class, 'index']
                )->name('index');

                Route::get(
                    '/create',
                    [SpinSubCampaignController::class, 'create']
                )->name('create');

                Route::post(
                    '/',
                    [SpinSubCampaignController::class, 'store']
                )->name('store');

                Route::get(
                    '/{subCampaign}/edit',
                    [SpinSubCampaignController::class, 'edit']
                )->name('edit');

                Route::put(
                    '/{subCampaign}',
                    [SpinSubCampaignController::class, 'update']
                )->name('update');

                Route::delete(
                    '/{subCampaign}',
                    [SpinSubCampaignController::class, 'destroy']
                )->name('destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | Special Cases
        |--------------------------------------------------------------------------
        */

        Route::post(
            'spin-campaigns/{spinCampaign}/special-cases',
            [SpinCampaignController::class, 'storeSpecialCase']
        )->name('spin-campaigns.special-cases.store');

        Route::delete(
            'spin-campaigns/{spinCampaign}/special-cases/{specialCase}',
            [SpinCampaignController::class, 'deleteSpecialCase']
        )->name('spin-campaigns.special-cases.destroy');

        /*
        |--------------------------------------------------------------------------
        | Reset Campaign Progress
        |--------------------------------------------------------------------------
        */

        Route::post(
            'spin-campaigns/{spinCampaign}/reset-progress',
            [SpinCampaignController::class, 'resetProgress']
        )->name('spin-campaigns.reset-progress');

        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/logout',
            [PortalAuthController::class, 'logout']
        )->name('logout');
    });