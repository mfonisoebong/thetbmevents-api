<?php

namespace App\Policies;

use App\Models\Payout;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PayoutPolicy
{
    use HandlesAuthorization;


    public function view(User $user, Payout $payout): bool
    {
        return $user->id === $payout->user_id;
    }

    public function create(User $user): bool
    {
        $bankDetailsId = request()->get('organizer_bank_details_id');
        return $user->bankDetails()->where('id', $bankDetailsId)->exists();
    }

    public function update(User $user, Payout $payout): bool
    {
        return $user?->admin_role === $payout->user_id;
    }
    
}
