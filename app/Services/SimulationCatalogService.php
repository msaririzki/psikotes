<?php

namespace App\Services;

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use App\Models\Attempt;
use App\Models\SimulationPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SimulationCatalogService
{
    public function overview(User $user): array
    {
        $packages = SimulationPackage::query()
            ->where('is_published', true)
            ->with([
                'packageSubtests.subtest.category',
            ])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $submittedAttempts = Attempt::query()
            ->where('user_id', $user->id)
            ->where('mode', AttemptModeEnum::SIMULATION)
            ->where('status', AttemptStatusEnum::SUBMITTED)
            ->latest('submitted_at')
            ->get();

        $inProgressAttempts = Attempt::query()
            ->where('user_id', $user->id)
            ->where('mode', AttemptModeEnum::SIMULATION)
            ->whereIn('status', [AttemptStatusEnum::DRAFT, AttemptStatusEnum::IN_PROGRESS])
            ->latest('started_at')
            ->get()
            ->keyBy('simulation_package_id');

        return [
            'summary' => [
                'packages' => $packages->count(),
                'attempts' => $submittedAttempts->count(),
                'best_score' => $submittedAttempts->max('score_total') !== null
                    ? (float) $submittedAttempts->max('score_total')
                    : null,
                'average_accuracy' => $submittedAttempts->avg('accuracy') !== null
                    ? round((float) $submittedAttempts->avg('accuracy'), 2)
                    : null,
            ],
            'packages' => $packages->map(
                fn (SimulationPackage $package): array => $this->serializePackageCard(
                    $package,
                    $inProgressAttempts->get($package->id),
                    $submittedAttempts->where('simulation_package_id', $package->id),
                ),
            )->all(),
            'recentAttempts' => $submittedAttempts
                ->take(8)
                ->map(fn (Attempt $attempt): array => $this->serializeAttemptHistory($attempt))
                ->all(),
        ];
    }

    public function packageDetail(SimulationPackage $simulationPackage, User $user): array
    {
        $simulationPackage->load([
            'packageSubtests.subtest.category',
            'packageSubtests.subtest.learningModules' => fn ($moduleQuery) => $moduleQuery
                ->where('is_published', true)
                ->orderBy('published_at')
                ->orderBy('title'),
        ]);

        $recentAttempts = Attempt::query()
            ->where('user_id', $user->id)
            ->where('mode', AttemptModeEnum::SIMULATION)
            ->where('simulation_package_id', $simulationPackage->id)
            ->where('status', AttemptStatusEnum::SUBMITTED)
            ->latest('submitted_at')
            ->get();

        $inProgressAttempt = Attempt::query()
            ->where('user_id', $user->id)
            ->where('mode', AttemptModeEnum::SIMULATION)
            ->where('simulation_package_id', $simulationPackage->id)
            ->whereIn('status', [AttemptStatusEnum::DRAFT, AttemptStatusEnum::IN_PROGRESS])
            ->latest('started_at')
            ->first();

        return [
            'simulationPackage' => [
                'id' => $simulationPackage->id,
                'title' => $simulationPackage->title,
                'slug' => $simulationPackage->slug,
                'description' => $simulationPackage->description,
                'instruction' => $simulationPackage->instruction,
                'duration_minutes' => $simulationPackage->duration_minutes,
                'question_count' => $simulationPackage->question_count,
                'subtests_count' => $simulationPackage->packageSubtests->count(),
                'analytics' => [
                    'attempts_count' => $recentAttempts->count(),
                    'best_score' => $recentAttempts->max('score_total') !== null
                        ? (float) $recentAttempts->max('score_total')
                        : null,
                    'average_accuracy' => $recentAttempts->avg('accuracy') !== null
                        ? round((float) $recentAttempts->avg('accuracy'), 2)
                        : null,
                ],
                'in_progress_attempt' => $inProgressAttempt
                    ? [
                        'id' => $inProgressAttempt->id,
                        'answered_questions' => $inProgressAttempt->answered_questions,
                        'total_questions' => $inProgressAttempt->total_questions,
                    ]
                    : null,
            ],
            'composition' => $simulationPackage->packageSubtests
                ->sortBy('sort_order')
                ->values()
                ->map(function ($packageSubtest): array {
                    $subtest = $packageSubtest->subtest;

                    return [
                        'subtest_id' => $subtest?->id,
                        'subtest_name' => $subtest?->name,
                        'category_name' => $subtest?->category?->name,
                        'question_count' => $packageSubtest->question_count,
                        'available_questions' => $this->eligibleQuestionsCount($subtest?->id),
                        'learning_modules' => $subtest?->learningModules?->take(2)->map(
                            fn ($module): array => [
                                'title' => $module->title,
                                'slug' => $module->slug,
                            ],
                        )->values()->all() ?? [],
                    ];
                })->all(),
            'recentAttempts' => $recentAttempts
                ->take(6)
                ->map(fn (Attempt $attempt): array => $this->serializeAttemptHistory($attempt))
                ->all(),
        ];
    }

    protected function serializePackageCard(
        SimulationPackage $simulationPackage,
        ?Attempt $inProgressAttempt,
        EloquentCollection|Collection $attempts,
    ): array {
        return [
            'id' => $simulationPackage->id,
            'title' => $simulationPackage->title,
            'slug' => $simulationPackage->slug,
            'description' => Str::limit((string) $simulationPackage->description, 180),
            'duration_minutes' => $simulationPackage->duration_minutes,
            'question_count' => $simulationPackage->question_count,
            'subtests_count' => $simulationPackage->packageSubtests->count(),
            'subtests' => $simulationPackage->packageSubtests
                ->sortBy('sort_order')
                ->take(3)
                ->values()
                ->map(fn ($packageSubtest): array => [
                    'name' => $packageSubtest->subtest?->name,
                    'question_count' => $packageSubtest->question_count,
                ])->all(),
            'in_progress_attempt' => $inProgressAttempt
                ? [
                    'id' => $inProgressAttempt->id,
                    'answered_questions' => $inProgressAttempt->answered_questions,
                    'total_questions' => $inProgressAttempt->total_questions,
                ]
                : null,
            'analytics' => [
                'attempts_count' => $attempts->count(),
                'best_score' => $attempts->max('score_total') !== null
                    ? (float) $attempts->max('score_total')
                    : null,
            ],
        ];
    }

    protected function serializeAttemptHistory(Attempt $attempt): array
    {
        $packageSnapshot = data_get($attempt->result_summary, 'package_snapshot', []);

        return [
            'id' => $attempt->id,
            'package_title' => $packageSnapshot['title'] ?? $attempt->simulationPackage?->title,
            'score_total' => $attempt->score_total !== null ? (float) $attempt->score_total : null,
            'accuracy' => $attempt->accuracy !== null ? (float) $attempt->accuracy : null,
            'correct_answers' => $attempt->correct_answers,
            'wrong_answers' => $attempt->wrong_answers,
            'blank_answers' => $attempt->blank_answers,
            'duration_seconds' => $attempt->duration_seconds,
            'submitted_at' => $attempt->submitted_at?->toIso8601String(),
        ];
    }

    protected function eligibleQuestionsCount(?int $subtestId): int
    {
        if (! $subtestId) {
            return 0;
        }

        return \App\Models\Question::query()
            ->where('subtest_id', $subtestId)
            ->where('status', \App\Enums\QuestionStatusEnum::PUBLISHED)
            ->whereIn('question_type', [
                \App\Enums\QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE,
                \App\Enums\QuestionTypeEnum::MULTIPLE_CHOICE_IMAGE,
                \App\Enums\QuestionTypeEnum::TRUE_FALSE,
            ])
            ->has('options', '>=', 2)
            ->whereHas('options', fn (Builder $query) => $query->where('is_correct', true))
            ->count();
    }
}
