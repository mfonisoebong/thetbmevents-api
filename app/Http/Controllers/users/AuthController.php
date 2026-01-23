<?php

namespace App\Http\Controllers\users;

use App\Events\PasswordTokenCreated;
use App\Events\UserRegistered;
use App\Http\Requests\LoginAdminWithIdRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordWithTokenRequest;
use App\Http\Requests\SendPasswordResetLinkRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Mail\OtpCode;
use App\Models\OtpVerification;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    use HttpResponses;

    public function getUser(Request $request)
    {
        $user = $request->user();
        $user-> first_name = explode(' ', $user->full_name)[0] ?? null;
        $user-> last_name = explode(' ', $user->full_name)[1] ?? null;
        $user->admin_role = $user->role === 'admin' ? 'super_admin' : null;

        return $user;
    }

    public function update(UpdateUserRequest $request)
    {
        $request->validated($request->all());

        $user = $request->user();
        $oldEmail = $user->email;
        $requestEmail = $request->email;

        $user->update($request->all());

        if ($oldEmail !== $requestEmail) {
            $user->email_verified_at = null;
            $user->save();

            $newOtp = OtpVerification::create([
                'user_id' => $user->id,
                'otp' => random_int(100000, 999999)
            ]);

            Mail::to($user->email)
                ->send(new OtpCode($user, $newOtp));
        }


        return $this->success($user);


    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $request->validated($request->all());
        $user = $request->user();
        $newPassword = Hash::make($request->password);

        $user->update([
            'password' => $newPassword
        ]);

        return $this->success(null, 'Updated password successfully');

    }

    public function login(LoginRequest $request)
    {
        $request->validated($request->all());


        $attempt_login = $this->validateUser(
            $request->email,
            $request->password,
        );

        if (!$attempt_login) {
            return $this->failed(401, null, 'Invalid credentials');
        }


        $user = User::where('email', $request->email)
            ->first();
        $token = $user
            ->createToken('Personal Access Token for ' . $request->email)
            ->plainTextToken;

        return $this
            ->success(['access_token' => $token], 'Logged in successfully');

    }

    public function loginAdminWithId(LoginAdminWithIdRequest $request)
    {

        $adminId = $request->id;
        $user = User::where('id', $adminId)
            ->first();
        if (!$user || $user->role !== 'admin') {
            return $this->failed(403);
        }
        $token = $user
            ->createToken('Personal Access Token for ' . $user->email)
            ->plainTextToken;

        return $this
            ->success(['access_token' => $token], 'Logged in successfully');
    }

    public function register(RegisterRequest $request)
    {
        $request->validated($request->all());

        $user = User::create([
            'business_name' => $request->business_name,
            'role' => $request->role,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
            'country' => $request->country,
            'phone_number' => $request->phone_number,
            'phone_dial_code' => $request->phone_dial_code
        ]);


        $token = $user
            ->createToken('Personal Access Token for ' . $request->email)
            ->plainTextToken;


        event(new UserRegistered($user));

        $request->
        session()
            ->regenerate();

        return $this
            ->success(['access_token' => $token], 'Logged in successfully');
    }

    public function googleLogin(Request $request)
    {
        $request->session()->put('route', 'login');

        return Socialite::driver('google')->redirect();
    }

    public function googleRegister(Request $request)
    {
        $request->session()->put('route', 'register');
        return Socialite::driver('google')->redirect();
    }

    public function googleCallback()
    {

        return $this->success([
            'access_token' => Auth::user()
                ->createToken('Personal Token')
                ->plainTextToken
        ]);
    }

    public function logout(Request $request)
    {
        auth()
            ->guard('web')
            ->logout();

        auth()
            ->user()
            ->tokens()
            ->delete();

        $request->session()
            ->regenerate(true);


        return $this->success(null, 'Logged out successfully');

    }

    public function resendOtpCode(Request $request)
    {

        $user = $request->user();

        $newOtp = OtpVerification::create([
            'user_id' => $user->id,
            'otp' => random_int(100000, 999999)
        ]);

        Mail::to($user->email)
            ->send(new OtpCode($user, $newOtp));


        return $this->success(null, 'Resent OTP');


    }

    public function verifyOtpCode(VerifyOtpRequest $request)
    {

        $request->validated($request->all());

        $user = $request->user();

        $otp = OtpVerification::where('otp', '=', $request->otp)
            ->where('user_id', '=', $user->id)
            ->first();


        if (!$otp) {
            return $this->failed(404, null, 'Otp is invalid');
        }

        // OTP expires after 30 mins
        $created = Carbon::parse($otp->created_at);
        $expired = $created->addMinutes(30);
        $now = Carbon::now();
        $hasExpired = $now->gt($expired);


        if ($hasExpired) {
            $otp->delete();
            return $this->failed(404, null, 'Otp code has expired');
        }

        $otp->delete();


        $user->email_verified_at = now();

        $user->save();


        return $this->success(null, 'Otp verified');


    }

    public function getResetTokenUser(PasswordResetToken $token)
    {
        return $this->success([
            'id' => $token->id,
            'email' => $token->user->email
        ]);
    }

    public function sendPasswordReset(SendPasswordResetLinkRequest $request)
    {

        $request->validated($request->all());

        $user = User::where('email', $request->email)
            ->first();

        if (!$user) {
            return $this->failed(400, null, 'User does not exist for this email');
        }

        $token = PasswordResetToken::create([
            'user_id' => $user->id
        ]);
        event(new PasswordTokenCreated($token));

        return $this->success(null, 'Password reset link has been sent');
    }

    public function resetPasswordWithToken(ResetPasswordWithTokenRequest $request)
    {

        $request->validated($request->all());

        $resetToken = PasswordResetToken::where('id', $request->token)
            ->first();

        if (!$resetToken) {
            return $this->failed(400, null, 'Password reset link has expired or is invalid');
        }

        $newPassword = Hash::make($request->password);
        $user = User::where('id', $resetToken->user->id)
            ->first();
        $user->update([
            'password' => $newPassword
        ]);
        $resetToken->delete();

        return $this->success(null, 'Password reset successfully');

    }

    private function validateUser($email, $password): bool
    {
        $user = User::where('email', $email)
            ->first();

        if (!$user)
            return false;

        $passwordCheck = Hash::check($password, $user->password);

        return $passwordCheck;

    }
}
