<?php

namespace App\Enums;

enum LearningLevelEnum: string
{
    case BEGINNER = 'beginner';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';

    public function label(): string
    {
        return match ($this) {
            self::BEGINNER => 'Pemula',
            self::INTERMEDIATE => 'Menengah',
            self::ADVANCED => 'Mahir',
        };
    }
}
