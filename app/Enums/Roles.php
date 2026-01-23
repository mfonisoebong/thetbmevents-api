<?php

namespace App\Enums;

enum Roles: int
{
    case customer = 1;
    case organizer = 2;
    case admin = 3;

    public static function fromString(string $str): ?Roles {
        return match($str) {
            'customer' => Roles::customer,
            'organizer' => Roles::organizer,
            'admin' => Roles::admin,
            default => null,
        };
    }
}
