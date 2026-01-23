<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\OTP as OTPModel;
use App\Models\User;

class OTPController extends Controller
{

    public static function generateAndSendOtp(User $user)
    {
        $otp = rand(1000, 9999); // Generate a 6-digit OTPController
        $expiresAt = now()->addMinutes(15); // Set expiry time (15 minutes)

        // Store OTPController in the database
        OTPModel::create([
            'user_id' => $user->id,
            'otp' => $otp,
            'expire_at' => $expiresAt
        ]);

        // Send OTPController via email
        Mail::send('emails.otp', ['otp' => $otp, 'user' => $user], function ($message) use ($user) {
            $message->to($user->email)->subject('Verify Your Enviable Logistics App (ELA) Account');
        });

        return response()->json(['message' => 'OTP sent successfully']);
    }

    public function verifyOtp(Request $request)
    {
        $otpRecord = OTPModel::where('otp', $request->otp)
            ->where('expire_at', '>', now())
            ->first();

        if ($otpRecord) {
            $user = User::find($otpRecord->user_id);
            $user->email_verified_at = now();
            $user->save();

            // Delete the OTPController record after successful verification
            $otpRecord->delete();

            // send welcome email
            Mail::send('emails.welcome', ['user' => $user], function ($message) use ($user) {
                $message->to($user->email)->subject('Welcome to Enviable Logistics App (ELA)! Your Delivery Partner');
            });

            return response()->json(['message' => 'Email verified successfully']);
        } else {
            return response()->json(['message' => 'Invalid or expired OTP'], 400);
        }
    }

    public function resendOtp(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            return self::generateAndSendOtp($user);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }
}
