<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\ForgotPasswordRequest;
use App\Http\Requests\V2\ResetPasswordByOtpRequest;
use App\Mail\OtpCode;
use App\Mail\PasswordChangedMail;
use App\Models\OtpVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    /**
     * POST /api/v2/auth/forgot-password
     *
     * Accepts email, generates password_reset OTP, emails it, returns 200.
     */
    public function sendResetPasswordOTPEmail(ForgotPasswordRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = User::where('email', $request->email)->firstOrFail();

            // Ensure only one active password reset OTP per user.
            $user->otpVerifications()->where('type', 'password_reset')->delete();

            $otp = $user->otpVerifications()->create([
                'otp' => rand(100000, 999999),
                'type' => 'password_reset',
            ]);

            Mail::to($user)->send(new OtpCode($user, $otp));
        });

        return response()->json([
            'message' => 'Password reset code sent successfully',
        ], 200);
    }

    /**
     * POST /api/v2/auth/forgot-password/reset
     *
     * Accepts otp + password + password_confirmation and resets password.
     */
    public function resetPasswordByOTP(ResetPasswordByOtpRequest $request)
    {
        $validated = $request->validated();

        $otp = OtpVerification::where('otp', $validated['otp'])
            ->where('type', 'password_reset')
            ->first();

        if (!$otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        $hasExpired = now()->gt(Carbon::parse($otp->expires_at));

        if ($hasExpired) {
            $otp->delete();
            return response()->json(['message' => 'OTP has expired'], 400);
        }

        $otp->user->update(['password' => Hash::make($validated['password'])]);
        $otp->delete();

        Mail::to($otp->user)->send(new PasswordChangedMail($otp->user));

        return response()->json([
            'message' => 'Password reset successfully',
        ], 200);
    }
}

