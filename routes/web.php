<?php

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {

    return view('welcome');
});


Route::get('/pdf-welcome', function () {
    $pdf = Pdf::loadView('welcome', [

    ]);
    return $pdf->download('welcome.pdf');

});

Route::get('/monitor', function () {
    $redisKey = config('monitoring.redis_key');
    $pageLimit = (int) config('monitoring.page_limit', 200);

    try {
        $logs = collect(Redis::lrange($redisKey, 0, max(0, $pageLimit - 1)))
            ->map(static function ($entry) {
                return json_decode($entry, true);
            })
            ->filter()
            ->values();

        return view('monitor', [
            'logs' => $logs,
            'redisKey' => $redisKey,
            'error' => null,
        ]);
    } catch (\Throwable $exception) {
        report($exception);

        return view('monitor', [
            'logs' => collect(),
            'redisKey' => $redisKey,
            'error' => 'Unable to read monitor data from Redis right now: ' . $exception->getMessage(),
        ]);
    }
});

