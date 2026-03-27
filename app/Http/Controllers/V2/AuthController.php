<?php

namespace App\Http\Controllers\V2;

use App\Events\ResendEmailVerificationLinkEvent;
use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\LoginRequest;
use App\Http\Requests\V2\SignUpRequest;
use App\Http\Resources\UserResource;
use App\Mail\OtpCode;
use App\Models\EmailLinkVerification;
use App\Models\OtpVerification;
use App\Models\User;
use App\Services\PhoneNumberVerifier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function signup(SignUpRequest $request)
    {
        $payload = $request->validated();

        if (!PhoneNumberVerifier::verifyPhoneNumber($payload['country'], $payload['phone_number'])) {
            return response()->json([
                'message' => 'The phone number is invalid for the selected country.',
            ], 422);
        }

        $user = User::create($payload);

        event(new UserRegistered($user, 'web'));

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

        $hasExpired = now()->gt(Carbon::parse($otp->expires_at));

        if ($hasExpired) {
            return $this->error('Otp has expired');
        }

        $user = User::find($otp->user_id);
        $user->email_verified_at = now();
        $user->save();
        $otp->delete();

        return $this->success(['token' => JWTAuth::fromUser($user), 'user' => $user], 'Email verified successfully');
    }

    public function resendEmailVerificationLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email']
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email is already verified'], 400);
        }

        $user->emailLinkVerifications()->delete();

        event(new ResendEmailVerificationLinkEvent($user));

        return response()->json([
            'message' => 'Verification link resent successfully'
        ]);
    }

    public function verifyEmailVerificationHash(string $hash)
    {
        $verification = EmailLinkVerification::where('hash', $hash)->first();

        if (!$verification) {
            return response()->json(['message' => 'Invalid verification link'], 400);
        }

        $hasExpired = now()->gt(Carbon::parse($verification->expires_at));

        if ($hasExpired) {
            return $this->error("Link has expired");
        }

        $user = User::find($verification->user_id);
        $user->email_verified_at = now();
        $user->save();
        $verification->delete();

        return response()->json([
            'message' => 'Email verified successfully',
            'token' => JWTAuth::fromUser($user),
            'user' => new UserResource($user)
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:255'
        ]);

        auth()->user()->update($request->only(['business_name', 'phone_number', 'email']));

        return $this->success();
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!password_verify($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->success(null, 'Password changed successfully');
    }
}
