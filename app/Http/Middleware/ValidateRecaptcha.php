<?php

namespace App\Http\Middleware;

use App\Traits\HttpResponses;
use Closure;
use Illuminate\Http\Request;
use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\Response;

class ValidateRecaptcha
{
    use HttpResponses;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {


        $token= $request->get('recaptcha_token');
        $recaptcha= new ReCaptcha(env('RECAPTCHA_SECRET'));
        $recaptcha->setScoreThreshold(0.7);
        $verify= $recaptcha->verify($token);
        $verified= $verify->isSuccess();



        if(!$verified){
            return $this->failed(403, null, 'Invalid recapctha token');
        }

        return $next($request);
    }
}
