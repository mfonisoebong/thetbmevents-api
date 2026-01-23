<?php

namespace App\Http\Controllers\Mobile\Auth;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\Mobile\Auth\ProfileResource;
use App\Mail\OtpCode;
use App\Mail\PasswordChangedMail;
use App\Models\EventCategory;
use App\Models\OtpVerification;
use App\Models\User;
use App\Models\UserPreferences;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {

        DB::beginTransaction();
        try {

            $user = User::create([
                'business_name' => $request->business_name,
                'role' => $request->role,
                'full_name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'country' => $request->country,
                'phone_number' => $request->phone_number,
            ]);


            $token = $user
                ->createToken('Personal Access Token for ' . $request->email)
                ->plainTextToken;

            DB::commit();

            event(new UserRegistered($user));

            $request->session()->regenerate();

            $data = [
                'token' => $token,
                'profile' => new ProfileResource($user),
            ];

            return $this
                ->success($data, 'Logged in successfully');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->failed(500, $e->getTrace(), $e->getMessage());
        }


    }

    public function verifyEmail(Request $request)
    {
        $validated = $request->validate([
            'otp' => ['required', 'string']
        ]);
        $user = $request->user();

        $otp = $user->otpVerifications()
            ->where('otp', $validated['otp'])
            ->where('type', 'email_verification')
            ->first();

        if (!$otp) {
            return $this->failed(404, null, 'Otp is invalid');
        }

        $hasExpired = now()->gt(Carbon::parse($otp->expires_at));

        if ($hasExpired) {
            return $this->failed(400, null, 'Otp has expired');
        }

        $user->email_verified_at = now();
        $user->save();
        $otp->delete();
        return $this->success(null, 'Email verified successfully');
    }

    public function resendEmailVerification(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $user = $request->user();
                $user->otpVerifications()->delete();
                $otp = $user->otpVerifications()->create([
                    'otp' => rand(100000, 999999),
                    'type' => 'email_verification'
                ]);
                Mail::to($user)->send(new OtpCode($user, $otp));
            });

            return $this->success(null, 'Email verification code sent successfully');
        } catch (\Throwable $e) {
            return $this->failed(500, $e->getTrace(), $e->getMessage());
        }
    }

    public function sendResetPasswordCode(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $validated = $request->validate([
                    'email' => ['required', 'email', 'exists:users,email']
                ]);
                $user = User::where('email', $validated['email'])->first();
                $otp = $user->otpVerifications()
                    ->where('type', 'password_reset')
                    ->create([
                        'otp' => rand(100000, 999999),
                        'type' => 'password_reset'
                    ]);
                Mail::to($user)->send(new OtpCode($user, $otp));
            });

            return $this->success(null, 'Password reset code sent successfully');
        } catch (\Throwable $e) {
            return $this->failed(500, null, $e->getMessage());
        }
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'otp' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed']
        ]);

        $otp = OtpVerification::where('otp', $validated['otp'])
            ->where('type', 'password_reset')
            ->first();
        if (!$otp) {
            return $this->failed(404, null, 'Otp is invalid');
        }
        $hasExpired = now()->gt(Carbon::parse($otp->expires_at));

        if ($hasExpired) {
            $otp->delete();
            return $this->failed(400, null, 'Otp has expired');
        }

        $otp->user->update(['password' => Hash::make($validated['password'])]);
        $otp->delete();

        Mail::to($otp->user)->send(new PasswordChangedMail($otp->user));

        return $this->success(null, 'Password reset successfully');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);


        $attempt_login = auth()->attempt([
            'email' => $request->email,
            'password' => $request->password
        ]);

        if (!$attempt_login) {
            return $this->failed(401, null, 'Invalid credentials');
        }


        $user = auth()->user();
        $token = $user
            ->createToken('Personal Access Token for ' . $request->email)
            ->plainTextToken;
        $data = [
            'token' => $token,
            'profile' => new ProfileResource($user),
        ];

        return $this
            ->success($data, 'Logged in successfully');

    }

    public function user(Request $request)
    {
        $user = $request->user();
        return $this->success(new ProfileResource($user), 'User profile retrieved successfully');
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully');
    }

    public function setPreferences(Request $request)
    {
        $request->validate([
            'category_ids' => ['required', 'array'],
            'category_ids.*' => 'exists:event_categories,id'
        ]);

        $categories = EventCategory::whereIn('id', $request->category_ids)->get();
        $categories->each(fn($category) => UserPreferences::create([
            'event_category_id' => $category->id,
            'user_id' => $request->user()->id
        ]));

        return $this->success(null, 'Preferences has been set');
    }
}
