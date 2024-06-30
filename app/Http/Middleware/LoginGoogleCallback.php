<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Traits\HttpResponses;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class LoginGoogleCallback
{
    use HttpResponses;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $googleUser = Socialite::driver('google')->user();
        $oldUser= User::where([
            'email' => $googleUser->email,
            'auth_provider'=> 'google'
        ])->first();



        if(!$oldUser){
            return redirect(env('CLIENT_URL').'/login?'."error=You don't have an account yet or your account was not registered with google");
        }
        $token= $oldUser
        ->createToken('Personal Token')
        ->plainTextToken;


        return redirect(env('CLIENT_URL').'/authenticate?token='.$token);
        }
}
