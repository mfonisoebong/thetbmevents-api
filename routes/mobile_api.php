<?php

use App\Http\Controllers\users\CouponController;
use App\Http\Controllers\users\EventsController;
use App\Http\Controllers\users\ProfileController;
use App\Http\Controllers\users\SalesController;
use App\Http\Controllers\users\TicketsController;
use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => 'App\Http\Controllers\Mobile',
    'prefix' => 'mobile',
], function () {

    Route::prefix('auth')->group(function () {
        Route::post('/login', 'Auth\AuthController@login');
        Route::post('/register', 'Auth\AuthController@register');
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', 'Auth\AuthController@login');
            Route::get('/user', 'Auth\AuthController@user');
        });
    });

    Route::prefix('events')->group(function () {
        Route::middleware(['auth:sanctum', 'role:organizer', 'account_state:active'])
            ->group(function () {
                Route::post('/send-blast-email', [EventsController::class, 'sendBlastEmail']);
                Route::get('/user', [EventsController::class, 'getUserEvents']);
                Route::get('/user/{event}', [EventsController::class, 'getUserEvent']);
                Route::post('/export', 'Event\EventsController@exportCsv');
                Route::post('/{event}/export/attendees', 'Event\EventsController@exportAttendeesCsv');
                Route::post('/store', [EventsController::class, 'store']);
                Route::post('/{event}', [EventsController::class, 'update']);
                Route::delete('/{event}', [EventsController::class, 'destroy']);

            });

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

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('sales')->group(function () {
            Route::get('/', [SalesController::class, 'getSales']);
            Route::post('/{sale}/resend-purchased-tickets', [SalesController::class, 'resendPurchasedTickets']);
        });

        Route::prefix('profile')->group(function () {
            Route::get('/overview', [ProfileController::class, 'getOverview']);
            Route::get('/finance/overview', [ProfileController::class, 'getFinanceOverview']);
            Route::post('/avatar/upload', [ProfileController::class, 'uploadAvatar']);
            Route::post('/avatar/remove', [ProfileController::class, 'removeAvatar']);
        });

    });

});

