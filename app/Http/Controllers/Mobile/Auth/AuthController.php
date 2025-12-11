<?php

namespace App\Http\Controllers\Mobile\Auth;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\Mobile\Auth\ProfileResource;
use App\Models\EventCategory;
use App\Models\User;
use App\Models\UserPreferences;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {

        DB::beginTransaction();
        try {

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

            DB::commit();

            $request->session()->regenerate();

            $data = [
                'token' => $token,
                'profile' => new ProfileResource($user),
            ];

            return $this
                ->success($data, 'Logged in successfully');
        } catch (\Exception | \Throwable $e) {
            DB::rollBack();
            return $this->failed(500, $e->getTrace(), $e->getMessage());
        }


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
