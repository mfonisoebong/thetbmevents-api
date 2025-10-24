<?php

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
            Route::post('/user', 'Auth\AuthController@user');
        });
    });

});

