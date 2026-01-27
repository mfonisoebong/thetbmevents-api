<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Event $event): bool
    {
        return $user->id === $event->user_id;
    }

    public function blastMail(User $user, Event $event): bool
    {
        return $user->id === $event->user_id;
    }
}
