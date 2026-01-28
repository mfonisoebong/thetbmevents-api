<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AdminOrganizersResource;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller
{

    public function listOrganizers()
    {
        return $this->success(AdminOrganizersResource::collection(User::where('role', 'organizer')->orderByDesc('created_at')->get()));
    }

    public function changeOrganizerStatus(string $organizer)
    {
        $organizer = User::findOrFail($organizer);

        $organizer->account_state = request()->input('status');
        $organizer->save();

        return $this->success('Organizer account status changed successfully.');
    }

    public function impersonateUser(User $user)
    {
        return $this->success([
            'token' => JWTAuth::fromUser($user),
            'user' => $user
        ]);
    }
}
