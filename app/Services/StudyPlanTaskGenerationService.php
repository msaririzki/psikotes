<?php

namespace App\Services;

use App\Enums\StudyPlanTaskEventTypeEnum;
use App\Enums\StudyPlanTaskStatusEnum;
use App\Models\StudyPlanTask;
use App\Models\User;
use Illuminate\Support\Collection;

class StudyPlanTaskGenerationService
{
    public function __construct(
        protected StudyCadenceService $studyCadenceService,
        protected StudyPlanTaskEventService $studyPlanTaskEventService,
    ) {}

    public function syncForUser(User $user, array $generatedPlan): Collection
    {
        $generatedTasks = $this->generatedTasks($generatedPlan);
        $taskKeys = $generatedTasks->pluck('id')->all();
        $cancelledTasks = StudyPlanTask::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where('status', '!=', StudyPlanTaskStatusEnum::COMPLETED->value)
            ->when(
                $taskKeys !== [],
                fn ($query) => $query->whereNotIn('task_key', $taskKeys),
                fn ($query) => $query,
            )
            ->get();

        foreach ($cancelledTasks as $cancelledTask) {
            $cancelledTask->forceFill(['is_active' => false])->save();

            $this->studyPlanTaskEventService->record(
                $cancelledTask,
                StudyPlanTaskEventTypeEnum::CANCELLED,
                'Task dibatalkan karena tidak lagi relevan pada plan terbaru.',
            );
        }

        foreach ($generatedTasks as $task) {
            $record = StudyPlanTask::query()->firstOrNew([
                'user_id' => $user->id,
                'task_key' => $task['id'],
            ]);

            $isNew = ! $record->exists;
            $cadence = $this->studyCadenceService->cadenceFor($task);
            $recommendedDate = $this->recommendedDate($task);

            $record->fill([
                'status' => $isNew ? StudyPlanTaskStatusEnum::PENDING : $record->status,
                'type' => $task['type'],
                'track' => $task['track'],
                'source' => $task['source'],
                'title' => $task['title'],
                'description' => $task['description'],
                'reason' => $task['reason'],
                'action_label' => $task['action_label'],
                'action_href' => $task['action_href'],
                'priority_score' => (int) $task['priority_score'],
                'priority_label' => $task['priority_label'] ?? null,
                'recommended_for_date' => $recommendedDate,
                'last_generated_at' => now(),
                'is_active' => true,
                'metadata' => array_filter([
                    ...($record->metadata ?? []),
                    'readiness_state' => data_get($generatedPlan, 'readiness.state'),
                    'generated_due_label' => $task['due_label'] ?? null,
                    'target' => $task['target'] ?? null,
                    'auto_resolve_on' => $task['auto_resolve_on'] ?? [],
                    'cadence' => $cadence,
                    'generation_snapshot' => [
                        'readiness_state' => data_get($generatedPlan, 'readiness.state'),
                        'readiness_label' => data_get($generatedPlan, 'readiness.label'),
                        'next_best_action_id' => data_get($generatedPlan, 'next_best_action.id'),
                        'next_best_action_title' => data_get($generatedPlan, 'next_best_action.title'),
                    ],
                ]),
            ]);

            if ($isNew || $record->scheduled_for_date === null) {
                $record->scheduled_for_date = $recommendedDate;
            }

            if ($record->status === StudyPlanTaskStatusEnum::SNOOZED && $record->snoozed_until?->lte(today())) {
                $record->status = StudyPlanTaskStatusEnum::PENDING;
                $record->snoozed_until = null;
            }

            $record->save();

            if ($isNew) {
                $this->studyPlanTaskEventService->record(
                    $record,
                    StudyPlanTaskEventTypeEnum::CREATED,
                    'Task baru masuk ke study plan user.',
                    [
                        'cadence' => $cadence,
                        'target' => $task['target'] ?? null,
                    ],
                );
            }
        }

        return StudyPlanTask::query()
            ->where('user_id', $user->id)
            ->with('events')
            ->latest('updated_at')
            ->get();
    }

    protected function generatedTasks(array $generatedPlan): Collection
    {
        return collect([
            data_get($generatedPlan, 'next_best_action'),
            ...data_get($generatedPlan, 'priority_recommendations', []),
            ...data_get($generatedPlan, 'review_queue', []),
            ...data_get($generatedPlan, 'plan_sections.immediate', []),
            ...data_get($generatedPlan, 'plan_sections.upcoming', []),
        ])
            ->filter()
            ->unique('id')
            ->values();
    }

    protected function recommendedDate(array $task): string
    {
        return $this->studyCadenceService->recommendedDate($task);
    }
}
