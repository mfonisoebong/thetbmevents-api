<?php

use App\Http\Controllers\V2\HlsController;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Http\Controllers\V2'], function () {
    Route::prefix('auth')->group(function () {
        Route::post('/signup', 'AuthController@signup');
        Route::post('/login', 'AuthController@login');

        Route::post('/forgot-password', 'PasswordResetController@sendResetPasswordOTPEmail');
        Route::post('/forgot-password/reset', 'PasswordResetController@resetPasswordByOTP');

        Route::post('/resend-email-otp', 'AuthController@resendEmailOtp');
        Route::post('/verify-email-otp', 'AuthController@verifyEmailOtp');

        Route::middleware('auth')->group(function () {
            Route::post('/logout', 'AuthController@logout');
            Route::post('/refresh', 'AuthController@refresh');
            Route::get('/me', 'AuthController@me');
            Route::put('/update-profile', 'AuthController@updateProfile');
            Route::post('/change-password', 'AuthController@changePassword');
        });
    });

    Route::prefix('events')->group(function () {
        Route::get('/', 'EventController@listRecentEvents');
        Route::get('/category/{category}', 'EventController@listRecentEventsByCategory');
        Route::get('/{event}', 'EventController@getEventDetails');
    });

    Route::prefix('checkout')->group(function () {
        Route::post('/', 'CheckoutController@processCheckout');
        Route::post('/apply-coupon', 'CheckoutController@applyCoupon');
    });

    Route::get('manual-verify-payment/{reference}', 'PaymentWebhookController@manualVerifyPayment');
    Route::prefix('webhooks')->group(function () {
        Route::post('/paystack', 'PaymentWebhookController@paystackWebhook');
        Route::post('/flutterwave', 'PaymentWebhookController@flutterwaveWebhook');
        Route::post('/flutterwave/failed', 'PaymentWebhookController@flutterwaveWebhookFailed');
    });

    /*
    |--------------------------------------------------------------------------
    | HLS streaming (served by Laravel to avoid nginx/CORS issues)
    |--------------------------------------------------------------------------
    |
    | These routes stream .m3u8 playlists and .ts segments from public/videos.
    |
    */
    Route::prefix('hls')->group(function () {
        Route::match(['GET', 'OPTIONS'], '/{path}', [HlsController::class, 'index'])->where('path', '.*');
    });

    Route::middleware(['auth', 'verified', 'active'])->prefix('dashboard')->group(function () {
        Route::prefix('organizer')->group(function () {
            Route::get('overview', 'OrganizerDashboardController@overview');
            Route::get('/event-orders-and-attendees/{event}', 'OrganizerDashboardController@eventOrdersAndAttendees');

            Route::prefix('event')->group(function () {
                Route::get('/', 'OrganizerEventController@index');
                Route::post('/', 'OrganizerEventController@createEvent');
                Route::put('/{event}', 'OrganizerEventController@updateEvent');
                Route::delete('/{event}/delete', 'OrganizerEventController@deleteEvent');
            });

            Route::prefix('ticket')->group(function () {
                Route::delete('/delete/{ticket}', 'OrganizerTicketController@deleteTicket');
                Route::put('/edit-end-date/{ticket}/{newEndDate}', 'OrganizerTicketController@editTicketEndDate');
            });

            Route::prefix('coupon')->group(function () {
                Route::get('/', 'OrganizerCouponController@index');
                Route::post('/', 'OrganizerCouponController@createCoupon');
                Route::put('/update-status/{coupon}', 'OrganizerCouponController@updateCouponStatus');
                Route::delete('/{coupon}', 'OrganizerCouponController@deleteCoupon');
            });

            Route::get('/revenue-by year/{year}', 'OrganizerDashboardController@revenueByYear');
            Route::get('/check-in-attendee/{newPurchasedTicket}', 'OrganizerDashboardController@checkInAttendee');
            Route::post('/send-blast-email', 'OrganizerDashboardController@sendBlastEmail');
        });

        Route::group(['prefix' => 'admin', 'middleware' => 'role:admin'], function () {
            Route::get('/overview', 'AdminController@overview');

            Route::get('/organizers', 'AdminController@listOrganizers');
            Route::put('/organizer/{user}/change-status', 'AdminController@changeOrganizerStatus');
            Route::post('/impersonate/{user}', 'AdminController@impersonateUser');

            Route::get('/attendees', 'AdminController@listAttendees');

            Route::prefix('finance')->group(function () {
                Route::get('/overview', 'AdminFinanceController@overview');
                Route::get('/verify-transaction/{reference}', 'AdminFinanceController@verifyTransaction');
            });

            Route::prefix('categories')->group(function () {
                Route::get('/', 'AdminCategoryController@index');
                Route::post('/', 'AdminCategoryController@createCategory');
                Route::delete('/{category}', 'AdminCategoryController@deleteCategory');
            });
        });
    });
});
