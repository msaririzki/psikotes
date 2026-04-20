<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case USER = 'user';
    case ADMIN = 'admin';
    case SUPER_ADMIN = 'super_admin';

    public function label(): string
    {
        return match ($this) {
            self::USER => 'Peserta',
            self::ADMIN => 'Admin',
            self::SUPER_ADMIN => 'Super Admin',
        };
    }

    public function isAdmin(): bool
    {
        return in_array($this, [self::ADMIN, self::SUPER_ADMIN], true);
    }
}
