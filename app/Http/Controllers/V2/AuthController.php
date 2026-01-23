<?php

namespace App\Http\Controllers\V2;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\LoginRequest;
use App\Http\Requests\V2\SignUpRequest;
use App\Http\Resources\UserResource;
use App\Mail\OtpCode;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function signup(SignUpRequest $request)
    {
        $user = User::create($request->validated());

        event(new UserRegistered($user));

        return response()->json([
            'message' => 'Successfully registered. Verify your email to complete registration',
            'user' => new UserResource($user)
        ], 201);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $loginRequest)
    {
        $credentials = $loginRequest->validated();

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'Incorrect email or password'], 401);
        }


        // 43200 minutes == 30 days
        $TTL = request()->remember ? 43200 : auth()->factory()->getTTL();

        $tokenToReturn = request()->has('remember') ? $this->longToken($TTL) : $token;

        return response()->json([
            'access_token' => $tokenToReturn,
            'token_type' => 'bearer',
            'expires_in' => $TTL * 60,
            'user' => auth()->user()
        ]);
    }


    private function longToken(int $customTTL)
    {
        return JWTAuth::claims(['exp' => now()->addMinutes($customTTL)->timestamp])->fromUser(auth()->user());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(new UserResource(auth()->user()));
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = auth()->refresh();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }

    public function resendEmailOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email']
        ]);

        $user = User::where('email', $request->email)->first();

        $user->otpVerifications()->where('type', 'email_verification')->delete();

        $otp = $user->otpVerifications()->create([
            'otp' => rand(100000, 999999),
            'type' => 'email_verification'
        ]);

        Mail::to($user)->send(new OtpCode($user, $otp));

        return response()->json([
            'message' => 'OTP resent successfully'
        ]);
    }

    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string']
        ]);

        $otp = OtpVerification::where('otp', $request->otp)
            ->where('type', 'email_verification')
            ->first();

        if (!$otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        $user = User::find($otp->user_id);
        $user->email_verified_at = now();
        $user->save();
        $otp->delete();

        return $this->success(['token' => JWTAuth::fromUser($user), 'user' => $user], 'Email verified successfully');
    }
}
