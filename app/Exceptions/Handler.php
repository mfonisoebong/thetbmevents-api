<?php

namespace App\Exceptions;

use App\Traits\HttpResponses;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    use HttpResponses;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    protected function shouldReturnJson($request, Throwable $e)
    {
        return true;
    }


    protected function unauthenticated($request, AuthenticationException $ex)
    {

        if ($request->is('api/*')) { // for routes starting with `/api`
            return $this->failed(401, null, 'Unauntenticated');
        }

        return redirect('/login'); // for normal routes
    }


    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
