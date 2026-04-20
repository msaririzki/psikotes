<?php

namespace App\Services;

use App\Enums\StudyPlanTaskEventTypeEnum;
use App\Enums\StudyPlanTaskStatusEnum;
use App\Models\StudyPlanTask;
use Illuminate\Support\Facades\DB;

class StudyPlanTaskTransitionService
{
    public function __construct(
        protected StudyPlanTaskEventService $studyPlanTaskEventService,
        protected StudyPlanOutcomeService $studyPlanOutcomeService,
    ) {}

    public function apply(StudyPlanTask $task, array $payload): StudyPlanTask
    {
        return DB::transaction(function () use ($task, $payload): StudyPlanTask {
            $action = $payload['action'];
            $impact = null;

            match ($action) {
                'done' => $impact = $this->markDone($task),
                'snooze' => $this->snooze($task, $payload['scheduled_for']),
                'reschedule' => $this->reschedule($task, $payload['scheduled_for']),
            };

            $task->metadata = [
                ...($task->metadata ?? []),
                'last_action' => $action,
                'last_transitioned_at' => now()->toIso8601String(),
                'last_manual_impact' => $impact['impact'] ?? data_get($task->metadata, 'last_manual_impact'),
            ];
            $task->save();

            return $task->refresh();
        });
    }

    protected function markDone(StudyPlanTask $task): array
    {
        $impact = $this->studyPlanOutcomeService->capture($task, $task->user);

        $task->status = StudyPlanTaskStatusEnum::COMPLETED;
        $task->completed_at = now();
        $task->completion_source = 'manual';
        $task->resolved_activity_type = null;
        $task->resolved_activity_id = null;
        $task->snoozed_until = null;

        $this->studyPlanTaskEventService->record(
            $task,
            StudyPlanTaskEventTypeEnum::MANUALLY_COMPLETED,
            'Task diselesaikan manual oleh user.',
            ['impact' => $impact],
        );

        return $impact;
    }

    protected function snooze(StudyPlanTask $task, string $scheduledFor): void
    {
        $task->status = StudyPlanTaskStatusEnum::SNOOZED;
        $task->scheduled_for_date = $scheduledFor;
        $task->snoozed_until = $scheduledFor;
        $task->completed_at = null;

        $this->studyPlanTaskEventService->record(
            $task,
            StudyPlanTaskEventTypeEnum::SNOOZED,
            'Task ditunda ke tanggal baru.',
            ['scheduled_for' => $scheduledFor],
        );
    }

    protected function reschedule(StudyPlanTask $task, string $scheduledFor): void
    {
        $task->status = StudyPlanTaskStatusEnum::RESCHEDULED;
        $task->scheduled_for_date = $scheduledFor;
        $task->snoozed_until = null;
        $task->completed_at = null;

        $this->studyPlanTaskEventService->record(
            $task,
            StudyPlanTaskEventTypeEnum::RESCHEDULED,
            'Task dijadwal ulang tanpa menandainya selesai.',
            ['scheduled_for' => $scheduledFor],
        );
    }
}
