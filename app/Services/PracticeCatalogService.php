<?php

namespace App\Services;

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use App\Enums\DifficultyEnum;
use App\Enums\PracticeDifficultyFilterEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Models\Attempt;
use App\Models\Category;
use App\Models\LearningModule;
use App\Models\Question;
use App\Models\Subtest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PracticeCatalogService
{
    public function overview(User $user): array
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->whereHas('subtests', fn (Builder $subtestQuery) => $this->applyPracticeReadySubtestScope($subtestQuery))
            ->with([
                'subtests' => function ($subtestQuery) {
                    $this->applyPracticeReadySubtestScope($subtestQuery);

                    $subtestQuery
                        ->with([
                            'learningModules' => fn ($moduleQuery) => $moduleQuery
                                ->where('is_published', true)
                                ->orderBy('published_at')
                                ->orderBy('title'),
                        ])
                        ->orderBy('sort_order')
                        ->orderBy('name');
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $submittedAttempts = Attempt::query()
            ->with('subtest.category')
            ->where('user_id', $user->id)
            ->where('mode', AttemptModeEnum::PRACTICE)
            ->where('status', AttemptStatusEnum::SUBMITTED)
            ->latest('submitted_at')
            ->get();

        $inProgressAttempts = Attempt::query()
            ->where('user_id', $user->id)
            ->where('mode', AttemptModeEnum::PRACTICE)
            ->whereIn('status', [AttemptStatusEnum::DRAFT, AttemptStatusEnum::IN_PROGRESS])
            ->latest('started_at')
            ->get()
            ->unique('subtest_id')
            ->keyBy('subtest_id');

        $analyticsMap = $submittedAttempts
            ->groupBy('subtest_id')
            ->map(fn (Collection $attempts): array => $this->serializePracticeAnalytics($attempts));

        return [
            'summary' => [
                'subtests' => $categories->sum(fn (Category $category) => $category->subtests->count()),
                'practice_attempts' => $submittedAttempts->count(),
                'best_score' => $submittedAttempts->max('score_total') !== null
                    ? (float) $submittedAttempts->max('score_total')
                    : null,
                'average_accuracy' => $submittedAttempts->avg('accuracy') !== null
                    ? round((float) $submittedAttempts->avg('accuracy'), 2)
                    : null,
                'in_progress_sessions' => $inProgressAttempts->count(),
            ],
            'categories' => $categories->map(
                fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'subtests' => $category->subtests->map(
                        fn (Subtest $subtest): array => $this->serializeSubtestCard(
                            $subtest,
                            $analyticsMap->get($subtest->id),
                            $inProgressAttempts->get($subtest->id),
                        ),
                    )->all(),
                ],
            )->all(),
            'recentAttempts' => $submittedAttempts
                ->take(8)
                ->map(fn (Attempt $attempt): array => $this->serializeAttemptHistory($attempt))
                ->all(),
        ];
    }

    public function subtestDetail(Subtest $subtest, User $user): array
    {
        $subtest->load([
            'category',
            'learningModules' => fn ($moduleQuery) => $moduleQuery
                ->where('is_published', true)
                ->orderBy('published_at')
                ->orderBy('title'),
        ]);

        $submittedAttempts = Attempt::query()
            ->where('user_id', $user->id)
            ->where('mode', AttemptModeEnum::PRACTICE)
            ->where('subtest_id', $subtest->id)
            ->where('status', AttemptStatusEnum::SUBMITTED)
            ->latest('submitted_at')
            ->get();

        $inProgressAttempt = Attempt::query()
            ->where('user_id', $user->id)
            ->where('mode', AttemptModeEnum::PRACTICE)
            ->where('subtest_id', $subtest->id)
            ->whereIn('status', [AttemptStatusEnum::DRAFT, AttemptStatusEnum::IN_PROGRESS])
            ->latest('started_at')
            ->first();

        $availability = $this->practiceAvailability($subtest);
        $difficultyOptions = collect(PracticeDifficultyFilterEnum::cases())
            ->map(fn (PracticeDifficultyFilterEnum $difficulty): array => [
                'value' => $difficulty->value,
                'label' => $difficulty->label(),
                'available_questions' => $availability[$difficulty->value] ?? 0,
            ])->all();

        return [
            'category' => [
                'name' => $subtest->category?->name,
                'slug' => $subtest->category?->slug,
            ],
            'subtest' => [
                'id' => $subtest->id,
                'name' => $subtest->name,
                'slug' => $subtest->slug,
                'description' => $subtest->description,
                'instruction' => $subtest->instruction,
                'default_duration_minutes' => $subtest->default_duration_minutes,
                'analytics' => $this->serializePracticeAnalytics($submittedAttempts),
                'availability' => $availability,
                'difficulty_options' => $difficultyOptions,
                'question_count_presets' => $this->questionCountPresets($availability['all']),
                'timer_options' => $this->timerOptions($subtest),
                'config_defaults' => [
                    'difficulty' => PracticeDifficultyFilterEnum::ALL->value,
                    'question_count' => $this->questionCountPresets($availability['all'])[0] ?? min($availability['all'], 10),
                    'timer_minutes' => null,
                ],
                'in_progress_attempt' => $inProgressAttempt
                    ? [
                        'id' => $inProgressAttempt->id,
                        'started_at' => $inProgressAttempt->started_at?->toIso8601String(),
                        'answered_questions' => $inProgressAttempt->answered_questions,
                        'total_questions' => $inProgressAttempt->total_questions,
                    ]
                    : null,
            ],
            'learningModules' => $subtest->learningModules->map(
                fn (LearningModule $learningModule): array => [
                    'id' => $learningModule->id,
                    'title' => $learningModule->title,
                    'slug' => $learningModule->slug,
                    'summary' => Str::limit((string) $learningModule->summary, 160),
                    'estimated_minutes' => $learningModule->estimated_minutes,
                    'level_label' => $learningModule->level?->label(),
                ],
            )->all(),
            'recentAttempts' => $submittedAttempts
                ->take(6)
                ->map(fn (Attempt $attempt): array => $this->serializeAttemptHistory($attempt))
                ->all(),
        ];
    }

    protected function applyPracticeReadySubtestScope(Builder|HasMany $subtestQuery): void
    {
        $subtestQuery
            ->where('is_active', true)
            ->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('is_active', true))
            ->whereHas('questions', function (Builder $questionQuery) {
                $this->applyEligiblePracticeQuestionScope($questionQuery);
            });
    }

    protected function practiceAvailability(Subtest $subtest): array
    {
        $allCount = $this->eligiblePracticeQuestionsQuery($subtest)->count();

        return [
            'all' => $allCount,
            'easy' => $this->eligiblePracticeQuestionsQuery($subtest, DifficultyEnum::EASY)->count(),
            'medium' => $this->eligiblePracticeQuestionsQuery($subtest, DifficultyEnum::MEDIUM)->count(),
            'hard' => $this->eligiblePracticeQuestionsQuery($subtest, DifficultyEnum::HARD)->count(),
        ];
    }

    protected function questionCountPresets(int $availableQuestions): array
    {
        if ($availableQuestions <= 0) {
            return [];
        }

        $presets = collect([5, 10, 15, 20, 25, 30])
            ->filter(fn (int $count) => $count <= $availableQuestions)
            ->values();

        if ($presets->isEmpty()) {
            return [$availableQuestions];
        }

        if (! $presets->contains($availableQuestions)) {
            $presets->push($availableQuestions);
        }

        return $presets->unique()->sort()->values()->all();
    }

    protected function timerOptions(Subtest $subtest): array
    {
        return collect([10, 15, 20, 30, 45, 60, $subtest->default_duration_minutes])
            ->filter()
            ->map(fn ($minutes) => (int) $minutes)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    protected function serializeSubtestCard(
        Subtest $subtest,
        ?array $analytics,
        ?Attempt $inProgressAttempt,
    ): array {
        $availability = $this->practiceAvailability($subtest);

        return [
            'id' => $subtest->id,
            'name' => $subtest->name,
            'slug' => $subtest->slug,
            'description' => $subtest->description,
            'instruction_excerpt' => Str::limit(trim(strip_tags((string) $subtest->instruction)), 140),
            'available_questions' => $availability['all'],
            'difficulty_breakdown' => [
                'easy' => $availability['easy'],
                'medium' => $availability['medium'],
                'hard' => $availability['hard'],
            ],
            'learning_modules' => $subtest->learningModules->take(2)->map(
                fn (LearningModule $learningModule): array => [
                    'title' => $learningModule->title,
                    'slug' => $learningModule->slug,
                ],
            )->all(),
            'analytics' => $analytics ?? $this->emptyAnalytics(),
            'in_progress_attempt' => $inProgressAttempt
                ? [
                    'id' => $inProgressAttempt->id,
                    'answered_questions' => $inProgressAttempt->answered_questions,
                    'total_questions' => $inProgressAttempt->total_questions,
                ]
                : null,
        ];
    }

    protected function serializePracticeAnalytics(EloquentCollection|Collection $attempts): array
    {
        if ($attempts->isEmpty()) {
            return $this->emptyAnalytics();
        }

        /** @var Attempt $latestAttempt */
        $latestAttempt = $attempts->sortByDesc('submitted_at')->first();

        return [
            'attempts_count' => $attempts->count(),
            'best_score' => $attempts->max('score_total') !== null
                ? (float) $attempts->max('score_total')
                : null,
            'average_accuracy' => $attempts->avg('accuracy') !== null
                ? round((float) $attempts->avg('accuracy'), 2)
                : null,
            'latest_score' => $latestAttempt->score_total !== null
                ? (float) $latestAttempt->score_total
                : null,
            'latest_accuracy' => $latestAttempt->accuracy !== null
                ? (float) $latestAttempt->accuracy
                : null,
            'last_submitted_at' => $latestAttempt->submitted_at?->toIso8601String(),
        ];
    }

    protected function emptyAnalytics(): array
    {
        return [
            'attempts_count' => 0,
            'best_score' => null,
            'average_accuracy' => null,
            'latest_score' => null,
            'latest_accuracy' => null,
            'last_submitted_at' => null,
        ];
    }

    protected function serializeAttemptHistory(Attempt $attempt): array
    {
        $configuration = data_get($attempt->result_summary, 'configuration', []);

        return [
            'id' => $attempt->id,
            'subtest' => [
                'name' => $attempt->subtest?->name,
                'slug' => $attempt->subtest?->slug,
            ],
            'score_total' => $attempt->score_total !== null ? (float) $attempt->score_total : null,
            'accuracy' => $attempt->accuracy !== null ? (float) $attempt->accuracy : null,
            'correct_answers' => $attempt->correct_answers,
            'wrong_answers' => $attempt->wrong_answers,
            'blank_answers' => $attempt->blank_answers,
            'duration_seconds' => $attempt->duration_seconds,
            'submitted_at' => $attempt->submitted_at?->toIso8601String(),
            'configuration' => [
                'difficulty_label' => $configuration['difficulty_label'] ?? 'Semua Level',
                'question_count' => $configuration['question_count'] ?? $attempt->total_questions,
                'timer_minutes' => $configuration['timer_minutes'] ?? null,
            ],
        ];
    }

    protected function eligiblePracticeQuestionsQuery(
        Subtest $subtest,
        ?DifficultyEnum $difficulty = null,
    ): Builder {
        return Question::query()
            ->where('subtest_id', $subtest->id)
            ->when(
                $difficulty,
                fn (Builder $questionQuery) => $questionQuery->where('difficulty', $difficulty),
            )
            ->tap(fn (Builder $questionQuery) => $this->applyEligiblePracticeQuestionScope($questionQuery));
    }

    protected function applyEligiblePracticeQuestionScope(Builder $questionQuery): void
    {
        $questionQuery
            ->where('status', QuestionStatusEnum::PUBLISHED)
            ->whereIn('question_type', $this->supportedPracticeQuestionTypes())
            ->has('options', '>=', 2)
            ->whereHas('options', fn (Builder $optionQuery) => $optionQuery->where('is_correct', true));
    }

    protected function supportedPracticeQuestionTypes(): array
    {
        return collect(QuestionTypeEnum::cases())
            ->filter(fn (QuestionTypeEnum $questionType) => $questionType->supportsPractice())
            ->map(fn (QuestionTypeEnum $questionType) => $questionType->value)
            ->all();
    }
}
