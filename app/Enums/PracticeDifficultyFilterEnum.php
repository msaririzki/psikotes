<?php

namespace App\Enums;

enum PracticeDifficultyFilterEnum: string
{
    case ALL = 'all';
    case EASY = 'easy';
    case MEDIUM = 'medium';
    case HARD = 'hard';

    public function label(): string
    {
        return match ($this) {
            self::ALL => 'Semua Level',
            self::EASY => 'Mudah',
            self::MEDIUM => 'Menengah',
            self::HARD => 'Sulit',
        };
    }

    public function questionDifficulty(): ?DifficultyEnum
    {
        return match ($this) {
            self::ALL => null,
            self::EASY => DifficultyEnum::EASY,
            self::MEDIUM => DifficultyEnum::MEDIUM,
            self::HARD => DifficultyEnum::HARD,
        };
    }
}

