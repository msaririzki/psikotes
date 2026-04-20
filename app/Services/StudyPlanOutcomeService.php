<?php

namespace App\Services;

use App\Models\StudyPlanTask;
use App\Models\User;

class StudyPlanOutcomeService
{
    public function __construct(
        protected StudyPlanGenerationService $studyPlanGenerationService,
    ) {}

    public function capture(StudyPlanTask $task, User $user): array
    {
        $before = [
            'readiness_state' => data_get($task->metadata, 'generation_snapshot.readiness_state'),
            'readiness_label' => data_get($task->metadata, 'generation_snapshot.readiness_label'),
            'next_best_action_id' => data_get($task->metadata, 'generation_snapshot.next_best_action_id'),
            'next_best_action_title' => data_get($task->metadata, 'generation_snapshot.next_best_action_title'),
        ];

        $afterPlan = $this->studyPlanGenerationService->forUser($user);

        return [
            'before' => $before,
            'after' => [
                'readiness_state' => data_get($afterPlan, 'readiness.state'),
                'readiness_label' => data_get($afterPlan, 'readiness.label'),
                'next_best_action_id' => data_get($afterPlan, 'next_best_action.id'),
                'next_best_action_title' => data_get($afterPlan, 'next_best_action.title'),
            ],
            'impact' => [
                'readiness_changed' => $before['readiness_state'] !== data_get($afterPlan, 'readiness.state'),
                'next_best_action_changed' => $before['next_best_action_id'] !== data_get($afterPlan, 'next_best_action.id'),
            ],
        ];
    }
}
