<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\LearningModule;
use App\Models\StudyPlanTask;

class StudyPlanTaskActivityMatcherService
{
    public const ACTIVITY_LEARNING_MODULE_VIEWED = 'learning_module_viewed';
    public const ACTIVITY_LEARNING_MODULE_COMPLETED = 'learning_module_completed';
    public const ACTIVITY_PRACTICE_SUBMITTED = 'practice_submitted';
    public const ACTIVITY_PRACTICE_RESULT_VIEWED = 'practice_result_viewed';
    public const ACTIVITY_SIMULATION_SUBMITTED = 'simulation_submitted';
    public const ACTIVITY_SIMULATION_RESULT_VIEWED = 'simulation_result_viewed';

    public function matches(StudyPlanTask $task, string $activityType, array $context): bool
    {
        $autoResolveOn = collect(data_get($task->metadata, 'auto_resolve_on', []));

        if (! $autoResolveOn->contains($activityType)) {
            return false;
        }

        return match ($activityType) {
            self::ACTIVITY_LEARNING_MODULE_VIEWED,
            self::ACTIVITY_LEARNING_MODULE_COMPLETED => $this->matchesLearningModule($task, $context['learning_module'] ?? null),
            self::ACTIVITY_PRACTICE_SUBMITTED,
            self::ACTIVITY_PRACTICE_RESULT_VIEWED => $this->matchesPractice($task, $context['attempt'] ?? null),
            self::ACTIVITY_SIMULATION_SUBMITTED,
            self::ACTIVITY_SIMULATION_RESULT_VIEWED => $this->matchesSimulation($task, $context['attempt'] ?? null),
            default => false,
        };
    }

    protected function matchesLearningModule(StudyPlanTask $task, ?LearningModule $learningModule): bool
    {
        if (! $learningModule) {
            return false;
        }

        $target = data_get($task->metadata, 'target', []);

        return (int) ($target['learning_module_id'] ?? 0) === $learningModule->id
            || ((int) ($target['subtest_id'] ?? 0) !== 0 && (int) ($target['subtest_id'] ?? 0) === $learningModule->subtest_id);
    }

    protected function matchesPractice(StudyPlanTask $task, ?Attempt $attempt): bool
    {
        if (! $attempt) {
            return false;
        }

        $target = data_get($task->metadata, 'target', []);

        return (int) ($target['subtest_id'] ?? 0) === (int) $attempt->subtest_id;
    }

    protected function matchesSimulation(StudyPlanTask $task, ?Attempt $attempt): bool
    {
        if (! $attempt) {
            return false;
        }

        $target = data_get($task->metadata, 'target', []);

        if ((int) ($target['attempt_id'] ?? 0) !== 0) {
            return (int) ($target['attempt_id'] ?? 0) === $attempt->id;
        }

        if ((int) ($target['subtest_id'] ?? 0) === 0) {
            return true;
        }

        $attempt->loadMissing('attemptQuestions');

        return $attempt->attemptQuestions->contains(function ($attemptQuestion) use ($target): bool {
            return (int) data_get($attemptQuestion->snapshot, 'question.subtest_id', 0) === (int) ($target['subtest_id'] ?? 0);
        });
    }
}
