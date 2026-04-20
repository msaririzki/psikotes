<?php

namespace App\Services;

use App\Enums\StudyPlanTaskEventTypeEnum;
use App\Models\StudyPlanTask;
use App\Models\StudyPlanTaskEvent;

class StudyPlanTaskEventService
{
    public function record(
        StudyPlanTask $task,
        StudyPlanTaskEventTypeEnum $eventType,
        ?string $description = null,
        array $payload = [],
    ): StudyPlanTaskEvent {
        return $task->events()->create([
            'user_id' => $task->user_id,
            'event_type' => $eventType,
            'description' => $description ?? $eventType->label(),
            'event_payload' => $payload === [] ? null : $payload,
            'happened_at' => now(),
        ]);
    }
}
