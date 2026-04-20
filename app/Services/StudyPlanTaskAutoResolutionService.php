<?php

namespace App\Services;

use App\Enums\StudyPlanTaskEventTypeEnum;
use App\Enums\StudyPlanTaskStatusEnum;
use App\Models\Attempt;
use App\Models\LearningModule;
use App\Models\StudyPlanTask;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudyPlanTaskAutoResolutionService
{
    public function __construct(
        protected StudyPlanTaskActivityMatcherService $matcher,
        protected StudyPlanTaskEventService $eventService,
        protected StudyPlanOutcomeService $outcomeService,
    ) {}

    public function learningModuleViewed(User $user, LearningModule $learningModule): void
    {
        $this->resolve($user, StudyPlanTaskActivityMatcherService::ACTIVITY_LEARNING_MODULE_VIEWED, [
            'learning_module' => $learningModule,
        ]);
    }

    public function learningModuleCompleted(User $user, LearningModule $learningModule): void
    {
        $this->resolve($user, StudyPlanTaskActivityMatcherService::ACTIVITY_LEARNING_MODULE_COMPLETED, [
            'learning_module' => $learningModule,
        ]);
    }

    public function practiceSubmitted(Attempt $attempt): void
    {
        $this->resolve($attempt->user, StudyPlanTaskActivityMatcherService::ACTIVITY_PRACTICE_SUBMITTED, [
            'attempt' => $attempt,
        ]);
    }

    public function practiceResultViewed(Attempt $attempt): void
    {
        $this->resolve($attempt->user, StudyPlanTaskActivityMatcherService::ACTIVITY_PRACTICE_RESULT_VIEWED, [
            'attempt' => $attempt,
        ]);
    }

    public function simulationSubmitted(Attempt $attempt): void
    {
        $this->resolve($attempt->user, StudyPlanTaskActivityMatcherService::ACTIVITY_SIMULATION_SUBMITTED, [
            'attempt' => $attempt,
        ]);
    }

    public function simulationResultViewed(Attempt $attempt): void
    {
        $this->resolve($attempt->user, StudyPlanTaskActivityMatcherService::ACTIVITY_SIMULATION_RESULT_VIEWED, [
            'attempt' => $attempt,
        ]);
    }

    protected function resolve(User $user, string $activityType, array $context): void
    {
        $tasks = $this->candidateTasks($user);

        foreach ($tasks as $task) {
            if (! $this->matcher->matches($task, $activityType, $context)) {
                continue;
            }

            DB::transaction(function () use ($task, $activityType, $context): void {
                $impact = $this->outcomeService->capture($task, $task->user);

                $task->forceFill([
                    'status' => StudyPlanTaskStatusEnum::COMPLETED,
                    'completed_at' => now(),
                    'completion_source' => 'auto',
                    'resolved_activity_type' => $activityType,
                    'resolved_activity_id' => $this->activityId($context),
                    'snoozed_until' => null,
                    'metadata' => [
                        ...($task->metadata ?? []),
                        'last_auto_resolution' => [
                            'activity_type' => $activityType,
                            'resolved_at' => now()->toIso8601String(),
                            'impact' => $impact['impact'],
                        ],
                    ],
                ])->save();

                $this->eventService->record(
                    $task,
                    StudyPlanTaskEventTypeEnum::AUTO_RESOLVED,
                    'Task selesai otomatis setelah aktivitas terkait benar-benar dikerjakan.',
                    [
                        'activity_type' => $activityType,
                        'activity_id' => $this->activityId($context),
                        'impact' => $impact,
                    ],
                );
            });
        }
    }

    protected function candidateTasks(User $user): Collection
    {
        return StudyPlanTask::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where('status', '!=', StudyPlanTaskStatusEnum::COMPLETED->value)
            ->get();
    }

    protected function activityId(array $context): ?int
    {
        $activity = $context['attempt'] ?? $context['learning_module'] ?? null;

        return $activity?->id;
    }
}
