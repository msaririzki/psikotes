<?php

namespace App\Enums;

enum StudyPlanTaskEventTypeEnum: string
{
    case CREATED = 'created';
    case SNOOZED = 'snoozed';
    case RESCHEDULED = 'rescheduled';
    case MANUALLY_COMPLETED = 'manually_completed';
    case AUTO_RESOLVED = 'auto_resolved';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::CREATED => 'Task dibuat',
            self::SNOOZED => 'Task ditunda',
            self::RESCHEDULED => 'Task dijadwal ulang',
            self::MANUALLY_COMPLETED => 'Task selesai manual',
            self::AUTO_RESOLVED => 'Task selesai otomatis',
            self::CANCELLED => 'Task dibatalkan',
        };
    }
}
