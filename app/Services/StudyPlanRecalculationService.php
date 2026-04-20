<?php

namespace App\Services;

use App\Models\StudyPlanTask;
use App\Models\User;
use Illuminate\Support\Collection;

class StudyPlanRecalculationService
{
    public function __construct(
        protected StudyPlanGenerationService $studyPlanGenerationService,
        protected StudyPlanTaskGenerationService $studyPlanTaskGenerationService,
        protected StudyPlanAgendaService $studyPlanAgendaService,
        protected ReadinessMilestoneCalculationService $readinessMilestoneCalculationService,
        protected GoalTrackingService $goalTrackingService,
    ) {}

    public function forUser(User $user): array
    {
        $generatedPlan = $this->studyPlanGenerationService->forUser($user);
        $tasks = $this->studyPlanTaskGenerationService->syncForUser($user, $generatedPlan);
        $agenda = $this->studyPlanAgendaService->build($tasks);
        $tasksByKey = $tasks->keyBy('task_key');
        $readinessProgress = $this->readinessMilestoneCalculationService->forUser($user, $generatedPlan['readiness'], $tasks);

        return [
            'readiness' => $generatedPlan['readiness'],
            'readiness_progress' => $readinessProgress,
            'goal_tracking' => $this->goalTrackingService->forUser($user, $generatedPlan, $tasks, $readinessProgress),
            'next_best_action' => $agenda['next_best_action'],
            'priority_recommendations' => $agenda['priority_recommendations'],
            'review_queue' => $this->selection(data_get($generatedPlan, 'review_queue', []), $tasksByKey),
            'plan_sections' => [
                'immediate' => collect($agenda['priority_recommendations'])->take(3)->values()->all(),
                'upcoming' => $agenda['upcoming'],
            ],
            'agenda' => $agenda['agenda'],
            'execution_summary' => $agenda['summary'],
            'review_cadence' => $agenda['review_cadence'],
            'transparency' => [
                ...$generatedPlan['transparency'],
                'Task yang selesai, ditunda, atau dijadwal ulang disimpan persisten dan memengaruhi agenda berikutnya.',
                'Task bisa selesai otomatis jika aktivitas belajar yang menjadi target benar-benar dikerjakan.',
            ],
        ];
    }

    protected function selection(array $generatedTasks, Collection $tasksByKey): array
    {
        return collect($generatedTasks)
            ->map(function (array $task) use ($tasksByKey): ?array {
                /** @var StudyPlanTask|null $record */
                $record = $tasksByKey->get($task['id']);

                return $record ? $this->studyPlanAgendaService->serialize($record) : null;
            })
            ->filter()
            ->values()
            ->all();
    }
}
