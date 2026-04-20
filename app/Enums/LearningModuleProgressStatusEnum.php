<?php

namespace App\Enums;

enum LearningModuleProgressStatusEnum: string
{
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'Sedang Dipelajari',
            self::COMPLETED => 'Selesai',
        };
    }
}
