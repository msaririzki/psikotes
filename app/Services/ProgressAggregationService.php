<?php

namespace App\Services;

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use App\Models\Attempt;
use App\Models\LearningModuleProgress;
use App\Models\User;
use Illuminate\Support\Collection;

class ProgressAggregationService
{
    public function __construct(
        protected SubtestAnalyticsService $subtestAnalyticsService,
        protected RecommendationGenerationService $recommendationGenerationService,
    ) {}

    public function dashboard(User $user): array
    {
        $submittedAttempts = Attempt::query()
            ->where('user_id', $user->id)
            ->where('status', AttemptStatusEnum::SUBMITTED)
            ->whereIn('mode', [
                AttemptModeEnum::MINI_QUIZ,
                AttemptModeEnum::PRACTICE,
                AttemptModeEnum::SIMULATION,
            ])
            ->with(['subtest', 'learningModule', 'simulationPackage'])
            ->latest('submitted_at')
            ->get();

        $moduleProgresses = LearningModuleProgress::query()
            ->where('user_id', $user->id)
            ->get();

        $subtestAnalytics = $this->subtestAnalyticsService->forUser($user);
        $recommendations = $this->recommendationGenerationService->generate($user, $subtestAnalytics);

        return [
            'summary' => [
                'learning_sessions' => $moduleProgresses->count(),
                'practice_attempts' => $submittedAttempts->where('mode', AttemptModeEnum::PRACTICE)->count(),
                'simulation_attempts' => $submittedAttempts->where('mode', AttemptModeEnum::SIMULATION)->count(),
                'average_score' => $submittedAttempts->avg('score_total') !== null
                    ? round((float) $submittedAttempts->avg('score_total'), 2)
                    : null,
                'average_accuracy' => $submittedAttempts->avg('accuracy') !== null
                    ? round((float) $submittedAttempts->avg('accuracy'), 2)
                    : null,
                'completed_modules' => $moduleProgresses->where('status', 'completed')->count(),
                'last_activity_at' => collect([
                    $moduleProgresses->max('last_viewed_at'),
                    $submittedAttempts->max('submitted_at'),
                ])->filter()->max()?->toIso8601String(),
            ],
            'insights' => [
                'strongest_area' => $subtestAnalytics['strongest'],
                'weakest_area' => $subtestAnalytics['weakest'],
            ],
            'trend' => $this->trend($submittedAttempts),
            'subtest_analytics' => $subtestAnalytics['items'],
            'recommendations' => $recommendations,
        ];
    }

    protected function trend(Collection $submittedAttempts): array
    {
        return $submittedAttempts
            ->take(8)
            ->sortBy('submitted_at')
            ->values()
            ->map(function (Attempt $attempt, int $index): array {
                $label = match ($attempt->mode) {
                    AttemptModeEnum::MINI_QUIZ => 'MQ',
                    AttemptModeEnum::PRACTICE => 'PR',
                    AttemptModeEnum::SIMULATION => 'SIM',
                };

                $context = match ($attempt->mode) {
                    AttemptModeEnum::MINI_QUIZ => $attempt->learningModule?->title,
                    AttemptModeEnum::PRACTICE => $attempt->subtest?->name,
                    AttemptModeEnum::SIMULATION => data_get($attempt->result_summary, 'package_snapshot.title', $attempt->simulationPackage?->title),
                };

                return [
                    'id' => $attempt->id,
                    'label' => $label.' '.($index + 1),
                    'mode' => $attempt->mode->value,
                    'mode_label' => match ($attempt->mode) {
                        AttemptModeEnum::MINI_QUIZ => 'Mini Quiz',
                        AttemptModeEnum::PRACTICE => 'Practice',
                        AttemptModeEnum::SIMULATION => 'Simulation',
                    },
                    'score' => $attempt->score_total !== null ? (float) $attempt->score_total : 0,
                    'accuracy' => $attempt->accuracy !== null ? (float) $attempt->accuracy : 0,
                    'occurred_at' => $attempt->submitted_at?->toIso8601String(),
                    'context' => $context,
                ];
            })
            ->all();
    }
}
