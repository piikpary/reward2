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
use App\Http\Controllers\Portal\AnnouncementController;
use App\Http\Controllers\Portal\SpecialSpinRewardController;
use App\Http\Controllers\Portal\SpinResultController;
use App\Http\Controllers\Portal\ShareCampaignController;
use App\Http\Controllers\Web\ShareCampaignViewController;
use App\Http\Controllers\Portal\ShareCampaignRewardController;
use App\Http\Controllers\Portal\RegistrationRewardSettingController;

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

Route::get(
            '/share-campaigns/{shareCampaign}',
            [ShareCampaignViewController::class, 'show']
    )->name('share-campaigns.public.show');

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

            Route::get(
                'registration-reward',
                [
                    RegistrationRewardSettingController::class,
                    'edit',
                ]
            )->name('registration-reward.edit');

            Route::put(
                'registration-reward',
                [
                    RegistrationRewardSettingController::class,
                    'update',
                ]
            )->name('registration-reward.update');

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
        | Share Campaigns
        |--------------------------------------------------------------------------
        */
        /*
        |--------------------------------------------------------------------------
        | Manual Share Campaign Rewards
        |--------------------------------------------------------------------------
        */

        Route::prefix('share-campaign-rewards')
            ->name('share-campaign-rewards.')
            ->controller(ShareCampaignRewardController::class)
            ->group(function (): void {
                Route::get(
                    '/',
                    'index'
                )->name('index');

                Route::post(
                    '/{progress}/grant',
                    'grant'
                )->name('grant');
            });

        Route::prefix('share-campaigns')
            ->name('share-campaigns.')
            ->controller(ShareCampaignController::class)
            ->group(function () {
                Route::get('/', 'index')
                    ->name('index');

                Route::get('/create', 'create')
                    ->name('create');

                Route::post('/', 'store')
                    ->name('store');

                Route::get(
                    '/{shareCampaign}/edit',
                    'edit'
                )->name('edit');

                Route::put(
                    '/{shareCampaign}',
                    'update'
                )->name('update');

                Route::patch(
                    '/{shareCampaign}/toggle-active',
                    'toggleActive'
                )->name('toggle-active');

                Route::patch(
                    '/{shareCampaign}/toggle-publish',
                    'togglePublish'
                )->name('toggle-publish');

                Route::get(
                    '/{shareCampaign}/shares',
                    'shares'
                )->name('shares');

                Route::delete(
                    '/{shareCampaign}',
                    'destroy'
                )->name('destroy');
                Route::get(
                    '/{shareCampaign}/shares',
                    'shares'
                )->name('shares');

                Route::patch(
                    '/{shareCampaign}/shares/{share}/approve',
                    'approveShare'
                )->name('shares.approve');

                Route::patch(
                    '/{shareCampaign}/shares/{share}/reject',
                    'rejectShare'
                )->name('shares.reject');
            });

        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/logout',
            [PortalAuthController::class, 'logout']
        )->name('logout');


        Route::resource(
            'announcements',
            AnnouncementController::class
        )->except(['show']);

        Route::patch(
            'announcements/{announcement}/toggle-status',
            [AnnouncementController::class, 'toggleStatus']
        )->name('announcements.toggle-status');

        Route::delete(
            'announcements/{announcement}/images/{image}',
            [AnnouncementController::class, 'deleteImage']
        )->name('announcements.images.delete');


        Route::get(
            '/special-spin-rewards',
            [SpecialSpinRewardController::class, 'index']
        )->name('special-spin-rewards.index');

        Route::post(
            '/special-spin-rewards/verify',
            [SpecialSpinRewardController::class, 'verify']
        )->name('special-spin-rewards.verify');

        

        Route::get(
            '/spin-results',
            [SpinResultController::class, 'index']
        )->name('spin-results.index');

        
    });