<?php

namespace App\Enums;

enum StudyPlanTaskStatusEnum: string
{
    case PENDING = 'pending';
    case SNOOZED = 'snoozed';
    case RESCHEDULED = 'rescheduled';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu',
            self::SNOOZED => 'Ditunda',
            self::RESCHEDULED => 'Dijadwal Ulang',
            self::COMPLETED => 'Selesai',
        };
    }

    public function isOpen(): bool
    {
        return $this !== self::COMPLETED;
    }
}
