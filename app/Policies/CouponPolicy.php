<?php

namespace App\Policies;

use App\Models\Coupon;
use App\Models\Event;
use App\Models\User;

class CouponPolicy
{
    /**
     * Determine whether the user can view any models.
     */


    public function before(User $user) {
        if($user->role === 'admin') {
            return true;
        }
    }


    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Coupon $coupon): bool
    {

        return $user->id === $coupon->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $eventId = request()->event_id;
        $event = Event::where('id', $eventId)->first();

        return $user->id === $event?->user_id;

    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Coupon $coupon): bool
    {
        return $user->id === $coupon->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Coupon $coupon): bool
    {
        return $user->id === $coupon->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Coupon $coupon): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Coupon $coupon): bool
    {
        //
    }
}
