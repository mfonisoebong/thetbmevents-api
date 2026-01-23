<?php

namespace App\Http\Middleware;

use App\Events\UserRegistered;
use App\Models\User;
use App\Traits\HttpResponses;
use Closure;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class RegisterGoogleCallback
{
    use HttpResponses;

    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {


        $route = $request->session()->get('route');

        $googleUser = Socialite::driver('google')->user();
        $oldUser = User::where('email', '=', $googleUser->email)->first();


        if ($route !== 'register') {
            return $next($request);
        }

        if ($oldUser) {
            return redirect(env('CLIENT_URL') . '/signup/organizer' . "?error=You already have an account yet");
        }
        $googleUser = Socialite::driver('google')->user();
        $user = User::create([
            'business_name' => $googleUser->name . ' buisness',
            'email' => $googleUser->email,
            'role' => 'organizer',
            'auth_provider' => 'google',
            'completed_profile' => false,
            'email_verified_at' => now(),
        ]);
        $token = $user
            ->createToken('Personal Token')
            ->plainTextToken;

        event(new UserRegistered($user));

        return redirect(env('CLIENT_URL') . '/authenticate?token=' . $token);

    }
}
