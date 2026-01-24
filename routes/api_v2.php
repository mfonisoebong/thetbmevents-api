<?php

use Illuminate\Support\Facades\Route;

Route::group(
    [
        'namespace' => 'App\Http\Controllers\V2',
        'where' => ['role' => '[a-zA-Z]+', 'id' => '[0-9]+', 'delivery' => '[0-9]+', 'errand' => '[0-9]+', 'hub' => '[0-9]+', 'walk_in' => '[0-9]+', 'tracking_number' => '[a-zA-Z0-9]+', 'reference' => '[a-zA-Z0-9-]+']
    ]
    , function () {
    Route::prefix('auth')->group(function () {
        Route::post('/signup', 'AuthController@signup');
        Route::post('/login', 'AuthController@login');

        // todo:implement
        Route::post('/forgot-password', 'PasswordResetController@sendResetOTPEmail');
        Route::post('/forgot-password/reset', 'PasswordResetController@resetByOTP');

        Route::post('/resend-email-otp', 'AuthController@resendEmailOtp');
        Route::post('/verify-email-otp', 'AuthController@verifyEmailOtp');

        Route::middleware('auth')->group(function () {
            Route::post('/logout', 'AuthController@logout');
            Route::post('/refresh', 'AuthController@refresh');
            Route::get('/me', 'AuthController@me');
            Route::post('/change-password', 'PasswordResetController@changePassword');
        });
    });

    Route::prefix('events')->group(function(){
        Route::get('/', 'EventController@listRecentEvents');
        Route::get('/category/{category}', 'EventController@listRecentEventsByCategory');
        Route::get('/{event}', 'EventController@getEventDetails');
    });
});
