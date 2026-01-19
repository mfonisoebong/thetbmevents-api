<?php

use App\Http\Controllers\admin\EventsController as AdminEventsController;
use App\Http\Controllers\users\AuthController;
use App\Http\Controllers\users\CouponController;
use App\Http\Controllers\users\EventsController;
use App\Http\Controllers\users\OrganizerBankDetailsController;
use App\Http\Controllers\users\PaymentController;
use App\Http\Controllers\users\ProfileController;
use App\Http\Controllers\users\TicketsController;
use App\Http\Middleware\AuthOrGuestMiddleware;
use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => 'App\Http\Controllers\Mobile',
    'prefix' => 'mobile',
], function () {

    Route::get('/', 'Health\HealthController');

    Route::prefix('auth')->group(function () {
        Route::post('/login', 'Auth\AuthController@login');
        Route::post('/register', 'Auth\AuthController@register');
        Route::post('/reset-password', 'Auth\AuthController@resetPassword');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', 'Auth\AuthController@login');
            Route::get('/user', 'Auth\AuthController@user');
            Route::post('/verify-email', 'Auth\AuthController@verifyEmail');
            Route::post('/resend-email-verification', 'Auth\AuthController@resendEmailVerification');
            Route::post('/send-password-reset-code', 'Auth\AuthController@sendResetPasswordCode');
            Route::put('/preferences', 'Auth\AuthController@setPreferences');
            Route::patch('/user', [AuthController::class, 'update']);
            Route::patch('/user/password', [AuthController::class, 'updatePassword']);
        });
    });

    Route::prefix('events')->group(function () {
        Route::middleware(['auth:sanctum', 'role:organizer', 'account_state:active'])
            ->group(function () {
                Route::post('/send-blast-email', [EventsController::class, 'sendBlastEmail']);
                Route::get('/user', 'Event\EventsController@getUserEvents');
                Route::get('/user/{event}', [EventsController::class, 'getUserEvent']);
                Route::post('/export', 'Event\EventsController@exportCsv');
                Route::post('/{event}/export/attendees', 'Event\EventsController@exportAttendeesCsv');
                Route::post('/', [EventsController::class, 'store']);
                Route::post('/{event}', [EventsController::class, 'update']);
                Route::delete('/{event}', [EventsController::class, 'destroy']);
            });

        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/recommendations/user', 'Event\EventsController@getUserRecommendations');
            Route::put('/{event}/likes', 'Event\EventsController@toggleLike');
        });

        Route::get('/', 'Event\EventsController@viewAll');
        Route::get('/categories', 'Event\CategoriesController@viewAll');
        Route::get('/featured', 'Event\EventsController@getFeaturedEvents');
        Route::get('/popular', 'Event\EventsController@getPopularEvents');
        Route::get('/recommendations', 'Event\EventsController@getRecommendations')
            ->middleware([AuthOrGuestMiddleware::class]);
        Route::get('/{event}', 'Event\EventsController@view');
    });

    Route::prefix('categories')->group(function () {
        Route::post('/', [AdminEventsController::class, 'store']);
        Route::post('/{category}', [AdminEventsController::class, 'update']);
        Route::delete('/{category}', [AdminEventsController::class, 'destroy']);
    })->middleware(['auth:sanctum', 'role:admin,manager']);

    Route::group(['prefix' => 'payments'], function () {
        Route::middleware(AuthOrGuestMiddleware::class)->group(function () {
            Route::post('/paystack', [PaymentController::class, 'paystackRedirectToGateway']);
            Route::post('/free', [PaymentController::class, 'freePayment']);
        });
        Route::get('/callback/{reference}', [PaymentController::class, 'callback']);
    });


    Route::prefix('coupons')
        ->group(function () {
            Route::middleware(['auth:sanctum', 'role:organizer', 'account_state:active'])->group(function () {
                Route::get('/', [CouponController::class, 'viewAll']);
                Route::get('/{coupon}', [CouponController::class, 'view']);
                Route::post('/', [CouponController::class, 'store']);
                Route::delete('/{coupon}', [CouponController::class, 'destroy']);
                Route::patch('/{coupon}', [CouponController::class, 'update']);
            });
        });

    Route::prefix('payouts')
        ->group(function () {
            Route::middleware(['auth:sanctum', 'role:organizer', 'account_state:active'])->group(function () {
                Route::post('/', 'Wallet\PayoutsController@store');
                Route::get('/', 'Wallet\PayoutsController@viewAll');
                Route::get('/wallet-info', 'Wallet\PayoutsController@viewWalletInfo');
            });
        });

    Route::prefix('tickets')->group(function () {
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::post('/', [TicketsController::class, 'store']);
            Route::patch('/{event}', [TicketsController::class, 'update']);
            Route::delete('/{ticket}', [TicketsController::class, 'destroy']);
            Route::middleware(['role:organizer', 'account_state:active'])->group(function () {
                Route::post('/verify/{ticket}', [TicketsController::class, 'verifyTicket']);
            });

        });
    });

    Route::group(['prefix' => 'bank-details', 'middleware' => ['auth:sanctum', 'role:organizer']], function () {
        Route::get('/', [OrganizerBankDetailsController::class, 'getBankDetails']);
        Route::post('/', [OrganizerBankDetailsController::class, 'store']);
        Route::patch('/', [OrganizerBankDetailsController::class, 'update']);
        Route::delete('/', [OrganizerBankDetailsController::class, 'destroy']);
    });

    Route::middleware('auth:sanctum', 'verified')->group(function () {
        Route::prefix('sales')->group(function () {
            Route::get('/', 'Sales\SalesController@getSales');
            Route::get('/overview', 'Sales\SalesController@overview');
            Route::get('/event/{event}', 'Sales\SalesController@getEventSales');
            Route::post('/{sale}/resend-purchased-tickets', 'Sales\SalesController@resendPurchasedTickets');
        });


        Route::prefix('dashboard')->group(function () {
            Route::get('/event-summary', 'Dashboard\DashboardController@eventSummary');
            Route::get('/event-preview', 'Dashboard\DashboardController@eventsPreview');
        });

        Route::prefix('profile')->group(function () {
            Route::get('/overview', [ProfileController::class, 'getOverview']);
            Route::get('/finance/overview', [ProfileController::class, 'getFinanceOverview']);
            Route::post('/avatar/upload', [ProfileController::class, 'uploadAvatar']);
            Route::post('/avatar/remove', [ProfileController::class, 'removeAvatar']);
        });

        Route::prefix('invoices')->group(function () {
            Route::get('/tickets', 'Transaction\InvoicesController@viewUserTickets');
            Route::get('/tickets/{ticket}/booking-details', 'Transaction\InvoicesController@viewBookingDetails');
            Route::get('/tickets/{ticket}/booking-details/download', 'Transaction\InvoicesController@downloadBookingDetails');
        });

    });

});

