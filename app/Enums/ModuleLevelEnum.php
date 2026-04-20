<?php

namespace App\Enums;

enum ModuleLevelEnum: string
{
    case BASIC = 'basic';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';

    public function label(): string
    {
        return match ($this) {
            self::BASIC => 'Dasar',
            self::INTERMEDIATE => 'Menengah',
            self::ADVANCED => 'Lanjutan',
        };
    }
}

