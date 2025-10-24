<?php

namespace App\Http\Controllers\Mobile\Auth;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\Auth\ProfileResource;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => ['required_if:role,customer,admin', 'string', 'max:255'],
            'last_name' => ['required_if:role,customer,admin', 'string', 'max:255'],
            'role' => ['required', Rule::in(['admin', 'organizer', 'customer'])],
            'buisness_name' => ['required_if:role,organizer', 'string', 'max:255'],
            'phone_number' => ['required'],
            'phone_dial_code' => ['required'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'min:6', 'max:30', 'confirmed'],
            'country' => ['required']
        ]);

        $user = User::create([
            'buisness_name' => $request->buisness_name,
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

        $data = [
            'token' => $token,
            'profile' => new ProfileResource($user),
        ];

        return $this
            ->success($data, 'Logged in successfully');
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
}
