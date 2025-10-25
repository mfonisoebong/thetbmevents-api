<?php

use App\Http\Controllers\users\EventsController;
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
            Route::post('/user', 'Auth\AuthController@user');
        });
    });

    Route::prefix('events')->group(function () {
        Route::middleware(['auth:sanctum', 'role:organizer', 'account_state:active'])
            ->group(function () {
                Route::get('/user', [EventsController::class, 'getUserEvents']);
                Route::get('/user/{event}', [EventsController::class, 'getUserEvent']);
                Route::post('/export', 'Event\EventsController@exportCsv');
                Route::post('/{event}/export/attendees', 'Event\EventsController@exportAttendeesCsv');
                Route::post('/store', [EventsController::class, 'store']);
                Route::post('/{event}', [EventsController::class, 'update']);
                Route::delete('/{event}', [EventsController::class, 'destroy']);

            });

    });

});

