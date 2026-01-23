<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\SignUpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function signup(SignUpRequest $request)
    {
        $user = User::create($request->validated());

        OTPController::generateAndSendOtp($user);

        return response()->json([
            'message' => 'User successfully registered',
            'user' => new UserResource($user)
        ], 201);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $loginRequest, $role = null)
    {
        $credentials = $loginRequest->validated();

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'Incorrect email or password'], 401);
        }

        // If role is provided, check if the user has the role
        $user = JWTAuth::user();
        if ($role && !$user->hasRole($role)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        // 43200 minutes == 30 days
        $TTL = request()->remember ? 43200 : ($role !== null ? 1440 : auth()->factory()->getTTL());

        return $this->respondWithToken(request()->has('remember') || $role !== null ? $this->longToken($TTL) : $token, $TTL);
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
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $customTTL = null)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => !$customTTL ? auth()->factory()->getTTL() * 60 : $customTTL * 60
        ]);
    }
}
