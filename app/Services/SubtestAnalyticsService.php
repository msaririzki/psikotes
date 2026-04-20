<?php

namespace App\Services;

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use App\Enums\LearningModuleProgressStatusEnum;
use App\Models\Attempt;
use App\Models\LearningModule;
use App\Models\LearningModuleProgress;
use App\Models\Subtest;
use App\Models\User;
use Illuminate\Support\Collection;

class SubtestAnalyticsService
{
    public function forUser(User $user): array
    {
        $attempts = Attempt::query()
            ->where('user_id', $user->id)
            ->where('status', AttemptStatusEnum::SUBMITTED)
            ->whereIn('mode', [
                AttemptModeEnum::MINI_QUIZ,
                AttemptModeEnum::PRACTICE,
                AttemptModeEnum::SIMULATION,
            ])
            ->with([
                'subtest.category',
                'learningModule.subtest.category',
                'attemptQuestions',
                'answers',
            ])
            ->orderBy('submitted_at')
            ->get();

        $moduleProgresses = LearningModuleProgress::query()
            ->where('user_id', $user->id)
            ->with('learningModule.subtest.category')
            ->get();

        $publishedModuleCounts = LearningModule::query()
            ->where('is_published', true)
            ->selectRaw('subtest_id, count(*) as aggregate')
            ->groupBy('subtest_id')
            ->pluck('aggregate', 'subtest_id');

        $analytics = [];

        foreach ($moduleProgresses as $progress) {
            $subtest = $progress->learningModule?->subtest;

            if (! $subtest) {
                continue;
            }

            $bucket = &$this->bucket($analytics, $subtest, (int) ($publishedModuleCounts[$subtest->id] ?? 0));
            $bucket['module_progress_count']++;
            $bucket['last_activity_at'] = $this->maxDate(
                $bucket['last_activity_at'],
                $progress->completed_at ?? $progress->last_viewed_at ?? $progress->started_at,
            );

            if ($progress->status === LearningModuleProgressStatusEnum::COMPLETED) {
                $bucket['completed_modules']++;
            }

            if ($progress->status === LearningModuleProgressStatusEnum::IN_PROGRESS) {
                $bucket['in_progress_modules']++;
            }
        }

        foreach ($attempts as $attempt) {
            if ($attempt->mode === AttemptModeEnum::SIMULATION) {
                $this->applySimulationAttempt($analytics, $attempt, $publishedModuleCounts);

                continue;
            }

            $subtest = $attempt->subtest ?? $attempt->learningModule?->subtest;

            if (! $subtest) {
                continue;
            }

            $bucket = &$this->bucket($analytics, $subtest, (int) ($publishedModuleCounts[$subtest->id] ?? 0));

            $this->applyAttemptMetrics($bucket, $attempt);
        }

        $rows = collect($analytics)
            ->map(fn (array $row): array => $this->finalizeRow($row))
            ->sortBy([
                ['average_accuracy', 'desc'],
                ['attempts_count', 'desc'],
                ['subtest_name', 'asc'],
            ])
            ->values();

        return [
            'items' => $rows->all(),
            'strongest' => $rows
                ->filter(fn (array $row): bool => $row['attempts_count'] > 0)
                ->sortByDesc('average_accuracy')
                ->first(),
            'weakest' => $rows
                ->filter(fn (array $row): bool => $row['attempts_count'] > 0)
                ->sortBy('average_accuracy')
                ->first(),
        ];
    }

    protected function applySimulationAttempt(array &$analytics, Attempt $attempt, Collection $publishedModuleCounts): void
    {
        $attempt->loadMissing('attemptQuestions', 'answers');

        $answersByQuestionId = $attempt->answers->keyBy('question_id');
        $questionGroups = $attempt->attemptQuestions->groupBy(
            fn ($attemptQuestion) => (int) data_get($attemptQuestion->snapshot, 'question.subtest_id', 0),
        );

        foreach ($questionGroups as $subtestId => $attemptQuestions) {
            $subtest = Subtest::query()->with('category')->find($subtestId);

            if (! $subtest) {
                continue;
            }

            $correct = 0;
            $wrong = 0;
            $blank = 0;

            foreach ($attemptQuestions as $attemptQuestion) {
                $answer = $answersByQuestionId->get($attemptQuestion->question_id);

                if (! $answer || $answer->selected_option_id === null) {
                    $blank++;

                    continue;
                }

                if ($answer->is_correct) {
                    $correct++;
                } else {
                    $wrong++;
                }
            }

            $totalQuestions = $attemptQuestions->count();
            $bucket = &$this->bucket($analytics, $subtest, (int) ($publishedModuleCounts[$subtest->id] ?? 0));

            $bucket['attempts_count']++;
            $bucket['simulation_attempts']++;
            $bucket['score_sum'] += $totalQuestions > 0 ? round(($correct / $totalQuestions) * 100, 2) : 0;
            $bucket['accuracy_sum'] += $totalQuestions > 0 ? round(($correct / $totalQuestions) * 100, 2) : 0;
            $bucket['duration_sum'] += (int) round(($attempt->duration_seconds ?? 0) / max($questionGroups->count(), 1));
            $bucket['correct_answers'] += $correct;
            $bucket['wrong_answers'] += $wrong;
            $bucket['blank_answers'] += $blank;
            $bucket['total_questions'] += $totalQuestions;
            $bucket['last_activity_at'] = $this->maxDate($bucket['last_activity_at'], $attempt->submitted_at);
            $bucket['recent_scores'][] = $totalQuestions > 0 ? round(($correct / $totalQuestions) * 100, 2) : 0;
        }
    }

    protected function applyAttemptMetrics(array &$bucket, Attempt $attempt): void
    {
        $bucket['attempts_count']++;
        $bucket['score_sum'] += (float) ($attempt->score_total ?? 0);
        $bucket['accuracy_sum'] += (float) ($attempt->accuracy ?? 0);
        $bucket['duration_sum'] += (int) ($attempt->duration_seconds ?? 0);
        $bucket['correct_answers'] += (int) $attempt->correct_answers;
        $bucket['wrong_answers'] += (int) $attempt->wrong_answers;
        $bucket['blank_answers'] += (int) $attempt->blank_answers;
        $bucket['total_questions'] += (int) $attempt->total_questions;
        $bucket['last_activity_at'] = $this->maxDate($bucket['last_activity_at'], $attempt->submitted_at);
        $bucket['recent_scores'][] = (float) ($attempt->accuracy ?? $attempt->score_total ?? 0);

        match ($attempt->mode) {
            AttemptModeEnum::MINI_QUIZ => $bucket['mini_quiz_attempts']++,
            AttemptModeEnum::PRACTICE => $bucket['practice_attempts']++,
            AttemptModeEnum::SIMULATION => $bucket['simulation_attempts']++,
        };
    }

    protected function finalizeRow(array $row): array
    {
        $attemptsCount = max($row['attempts_count'], 0);
        $averageScore = $attemptsCount > 0
            ? round($row['score_sum'] / $attemptsCount, 2)
            : null;
        $averageAccuracy = $attemptsCount > 0
            ? round($row['accuracy_sum'] / $attemptsCount, 2)
            : null;
        $averageDuration = $attemptsCount > 0
            ? (int) round($row['duration_sum'] / $attemptsCount)
            : null;
        $recentScores = collect($row['recent_scores'])
            ->take(-4)
            ->values();
        $trendDelta = $recentScores->count() >= 2
            ? round($recentScores->last() - $recentScores->first(), 2)
            : 0.0;
        $trendDirection = $trendDelta >= 5
            ? 'improving'
            : ($trendDelta <= -5 ? 'declining' : 'stable');
        $blankRate = $row['total_questions'] > 0
            ? round(($row['blank_answers'] / $row['total_questions']) * 100, 2)
            : 0;

        return [
            'subtest_id' => $row['subtest_id'],
            'subtest_name' => $row['subtest_name'],
            'subtest_slug' => $row['subtest_slug'],
            'category_name' => $row['category_name'],
            'attempts_count' => $attemptsCount,
            'mini_quiz_attempts' => $row['mini_quiz_attempts'],
            'practice_attempts' => $row['practice_attempts'],
            'simulation_attempts' => $row['simulation_attempts'],
            'average_score' => $averageScore,
            'average_accuracy' => $averageAccuracy,
            'average_duration_seconds' => $averageDuration,
            'correct_answers' => $row['correct_answers'],
            'wrong_answers' => $row['wrong_answers'],
            'blank_answers' => $row['blank_answers'],
            'blank_rate' => $blankRate,
            'module_progress_count' => $row['module_progress_count'],
            'completed_modules' => $row['completed_modules'],
            'in_progress_modules' => $row['in_progress_modules'],
            'published_modules_count' => $row['published_modules_count'],
            'completion_rate' => $row['published_modules_count'] > 0
                ? round(($row['completed_modules'] / $row['published_modules_count']) * 100, 2)
                : 0,
            'last_activity_at' => $row['last_activity_at']?->toIso8601String(),
            'recent_scores' => $recentScores->all(),
            'trend' => [
                'delta' => $trendDelta,
                'direction' => $trendDirection,
            ],
            'status' => $averageAccuracy === null
                ? 'not_enough_data'
                : ($averageAccuracy >= 80
                    ? 'strong'
                    : ($averageAccuracy < 60 || $blankRate >= 25 ? 'weak' : 'developing')),
        ];
    }

    protected function &bucket(array &$analytics, Subtest $subtest, int $publishedModulesCount): array
    {
        if (! isset($analytics[$subtest->id])) {
            $analytics[$subtest->id] = [
                'subtest_id' => $subtest->id,
                'subtest_name' => $subtest->name,
                'subtest_slug' => $subtest->slug,
                'category_name' => $subtest->category?->name,
                'attempts_count' => 0,
                'mini_quiz_attempts' => 0,
                'practice_attempts' => 0,
                'simulation_attempts' => 0,
                'score_sum' => 0.0,
                'accuracy_sum' => 0.0,
                'duration_sum' => 0,
                'correct_answers' => 0,
                'wrong_answers' => 0,
                'blank_answers' => 0,
                'total_questions' => 0,
                'module_progress_count' => 0,
                'completed_modules' => 0,
                'in_progress_modules' => 0,
                'published_modules_count' => $publishedModulesCount,
                'last_activity_at' => null,
                'recent_scores' => [],
            ];
        }

        return $analytics[$subtest->id];
    }

    protected function maxDate($left, $right)
    {
        if (! $left) {
            return $right;
        }

        if (! $right) {
            return $left;
        }

        return $left->greaterThan($right) ? $left : $right;
    }
}
