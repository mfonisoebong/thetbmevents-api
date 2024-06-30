<?php

namespace App\Http\Controllers\users;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function verifyEmail(EmailVerificationRequest  $request){
        $request->fulfill();
        return redirect(env('CLIENT_URL'));
    }

    public function resendVerification(Request $request){
        $request
        ->user()
        ->sendEmailVerificationNotification();

    }
}
