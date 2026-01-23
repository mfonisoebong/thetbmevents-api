<?php

namespace App\Arrays;

class UserRoles
{
    public static function get(): array
    {
        return ['customer', 'organizer', 'admin'];
    }
}
