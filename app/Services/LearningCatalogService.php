<?php

namespace App\Services;

use App\Enums\QuestionStatusEnum;
use App\Models\Attempt;
use App\Models\Category;
use App\Models\LearningModule;
use App\Models\Question;
use App\Models\Subtest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LearningCatalogService
{
    public function __construct(
        protected LearningProgressService $progressService,
    ) {}

    public function overview(User $user): array
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->whereHas('subtests', function ($subtestQuery) {
                $subtestQuery
                    ->where('is_active', true)
                    ->whereHas('learningModules', fn ($moduleQuery) => $moduleQuery->where('is_published', true));
            })
            ->with([
                'subtests' => function ($subtestQuery) {
                    $subtestQuery
                        ->where('is_active', true)
                        ->whereHas('learningModules', fn ($moduleQuery) => $moduleQuery->where('is_published', true))
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

        $moduleIds = $categories
            ->flatMap(fn (Category $category) => $category->subtests)
            ->flatMap(fn (Subtest $subtest) => $subtest->learningModules)
            ->pluck('id')
            ->all();

        $progressMap = $this->progressService->progressMapForUser($user, $moduleIds);

        return [
            'summary' => $this->catalogSummary($categories, $progressMap),
            'categories' => $categories->map(
                fn (Category $category): array => $this->serializeCategoryCard($category, $progressMap),
            )->all(),
            'recentMiniQuizzes' => $this->serializeRecentMiniQuizzes(
                Attempt::query()
                    ->with('learningModule:id,title,slug')
                    ->where('user_id', $user->id)
                    ->where('mode', 'mini_quiz')
                    ->whereNotNull('submitted_at')
                    ->latest('submitted_at')
                    ->limit(5)
                    ->get(),
            ),
        ];
    }

    public function categoryDetail(Category $category, User $user): array
    {
        $category->load([
            'subtests' => function ($subtestQuery) {
                $subtestQuery
                    ->where('is_active', true)
                    ->whereHas('learningModules', fn ($moduleQuery) => $moduleQuery->where('is_published', true))
                    ->with([
                        'learningModules' => fn ($moduleQuery) => $moduleQuery
                            ->where('is_published', true)
                            ->orderBy('published_at')
                            ->orderBy('title'),
                    ])
                    ->orderBy('sort_order')
                    ->orderBy('name');
            },
        ]);

        $moduleIds = $category->subtests
            ->flatMap(fn (Subtest $subtest) => $subtest->learningModules)
            ->pluck('id')
            ->all();

        $progressMap = $this->progressService->progressMapForUser($user, $moduleIds);

        return [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'progress' => $this->summaryFromModuleIds($moduleIds, $progressMap),
            ],
            'subtests' => $category->subtests->map(
                fn (Subtest $subtest): array => $this->serializeSubtestCard($subtest, $progressMap),
            )->all(),
        ];
    }

    public function subtestDetail(Category $category, Subtest $subtest, User $user): array
    {
        $subtest->load([
            'learningModules' => fn ($moduleQuery) => $moduleQuery
                ->where('is_published', true)
                ->orderBy('published_at')
                ->orderBy('title'),
        ]);

        $moduleIds = $subtest->learningModules->pluck('id')->all();
        $progressMap = $this->progressService->progressMapForUser($user, $moduleIds);

        $recentAttempts = Attempt::query()
            ->with('learningModule:id,title,slug')
            ->where('user_id', $user->id)
            ->where('mode', 'mini_quiz')
            ->where('subtest_id', $subtest->id)
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->limit(5)
            ->get();

        return [
            'category' => [
                'name' => $category->name,
                'slug' => $category->slug,
            ],
            'subtest' => [
                'id' => $subtest->id,
                'name' => $subtest->name,
                'slug' => $subtest->slug,
                'description' => $subtest->description,
                'instruction' => $subtest->instruction,
                'progress' => $this->summaryFromModuleIds($moduleIds, $progressMap),
            ],
            'modules' => $subtest->learningModules->map(
                fn (LearningModule $learningModule): array => $this->serializeModuleCard($learningModule, $progressMap),
            )->all(),
            'recentMiniQuizzes' => $this->serializeRecentMiniQuizzes($recentAttempts),
        ];
    }

    public function moduleDetail(LearningModule $learningModule, User $user): array
    {
        $learningModule->load([
            'subtest.category',
            'progresses' => fn ($progressQuery) => $progressQuery->where('user_id', $user->id),
        ]);

        $progress = $learningModule->progresses->first();
        $exampleQuestions = Question::query()
            ->with(['options' => fn ($optionQuery) => $optionQuery->orderBy('sort_order')->orderBy('option_key')])
            ->where('subtest_id', $learningModule->subtest_id)
            ->where('status', QuestionStatusEnum::PUBLISHED)
            ->orderBy('id')
            ->get()
            ->filter(fn (Question $question) => $question->canBeUsedForMiniQuiz())
            ->take(2)
            ->values();

        $quizEligibleQuestions = Question::query()
            ->with(['options' => fn ($optionQuery) => $optionQuery->orderBy('sort_order')->orderBy('option_key')])
            ->where('subtest_id', $learningModule->subtest_id)
            ->where('status', QuestionStatusEnum::PUBLISHED)
            ->orderBy('id')
            ->get()
            ->filter(fn (Question $question) => $question->canBeUsedForMiniQuiz())
            ->take(5);

        $recentAttempts = Attempt::query()
            ->where('user_id', $user->id)
            ->where('mode', 'mini_quiz')
            ->where('learning_module_id', $learningModule->id)
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->limit(5)
            ->get();

        $nextModule = LearningModule::query()
            ->where('subtest_id', $learningModule->subtest_id)
            ->where('is_published', true)
            ->where('id', '!=', $learningModule->id)
            ->where('id', '>', $learningModule->id)
            ->orderBy('id')
            ->first();

        return [
            'category' => [
                'name' => $learningModule->subtest?->category?->name,
                'slug' => $learningModule->subtest?->category?->slug,
            ],
            'subtest' => [
                'name' => $learningModule->subtest?->name,
                'slug' => $learningModule->subtest?->slug,
                'instruction' => $learningModule->subtest?->instruction,
            ],
            'module' => [
                'id' => $learningModule->id,
                'title' => $learningModule->title,
                'slug' => $learningModule->slug,
                'summary' => $learningModule->summary,
                'content' => $learningModule->content,
                'tips' => $learningModule->tips,
                'tricks' => $learningModule->tricks,
                'level' => $learningModule->level?->value,
                'level_label' => $learningModule->level?->label(),
                'estimated_minutes' => $learningModule->estimated_minutes,
                'progress' => $this->progressService->serialize($progress),
                'objectives' => [
                    'Memahami konsep dasar '.$learningModule->subtest?->name.' dari modul ini.',
                    'Mengenali pola soal dan instruksi kerja yang paling sering muncul.',
                    'Menyiapkan strategi menjawab sebelum masuk ke mini quiz.',
                ],
                'quiz_available' => $quizEligibleQuestions->isNotEmpty(),
                'quiz_question_count' => $quizEligibleQuestions->count(),
                'next_module' => $nextModule
                    ? [
                        'title' => $nextModule->title,
                        'slug' => $nextModule->slug,
                    ]
                    : null,
            ],
            'examples' => $exampleQuestions->map(
                fn (Question $question): array => [
                    'id' => $question->id,
                    'code' => $question->code,
                    'question_text' => $question->question_text,
                    'options' => $question->options->map(
                        fn ($option): array => [
                            'id' => $option->id,
                            'option_key' => $option->option_key,
                            'option_text' => $option->option_text,
                            'is_correct' => (bool) $option->is_correct,
                        ],
                    )->values()->all(),
                    'explanation_text' => $question->explanation_text,
                ],
            )->all(),
            'recentMiniQuizzes' => $this->serializeRecentMiniQuizzes($recentAttempts),
        ];
    }

    protected function catalogSummary(EloquentCollection $categories, Collection $progressMap): array
    {
        $moduleIds = $categories
            ->flatMap(fn (Category $category) => $category->subtests)
            ->flatMap(fn (Subtest $subtest) => $subtest->learningModules)
            ->pluck('id')
            ->all();

        return [
            'categories' => $categories->count(),
            'subtests' => $categories->sum(fn (Category $category) => $category->subtests->count()),
            'modules' => count($moduleIds),
            ...$this->summaryFromModuleIds($moduleIds, $progressMap),
        ];
    }

    protected function summaryFromModuleIds(array $moduleIds, Collection $progressMap): array
    {
        $completed = 0;
        $inProgress = 0;

        foreach ($moduleIds as $moduleId) {
            $progress = $progressMap->get($moduleId);

            if (! $progress) {
                continue;
            }

            if ($progress->status?->value === 'completed') {
                $completed++;

                continue;
            }

            $inProgress++;
        }

        $total = count($moduleIds);

        return [
            'completed' => $completed,
            'in_progress' => $inProgress,
            'not_started' => max($total - $completed - $inProgress, 0),
            'completion_rate' => $total > 0
                ? (int) round(($completed / $total) * 100)
                : 0,
        ];
    }

    protected function serializeCategoryCard(Category $category, Collection $progressMap): array
    {
        $moduleIds = $category->subtests
            ->flatMap(fn (Subtest $subtest) => $subtest->learningModules)
            ->pluck('id')
            ->all();

        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'progress' => $this->summaryFromModuleIds($moduleIds, $progressMap),
            'subtests' => $category->subtests->map(
                fn (Subtest $subtest): array => $this->serializeSubtestCard($subtest, $progressMap),
            )->all(),
        ];
    }

    protected function serializeSubtestCard(Subtest $subtest, Collection $progressMap): array
    {
        $moduleIds = $subtest->learningModules->pluck('id')->all();

        return [
            'id' => $subtest->id,
            'name' => $subtest->name,
            'slug' => $subtest->slug,
            'description' => $subtest->description,
            'instruction_excerpt' => Str::limit(trim(strip_tags((string) $subtest->instruction)), 160),
            'progress' => $this->summaryFromModuleIds($moduleIds, $progressMap),
            'modules_count' => $subtest->learningModules->count(),
            'featured_modules' => $subtest->learningModules->take(3)->map(
                fn (LearningModule $learningModule): array => [
                    'id' => $learningModule->id,
                    'title' => $learningModule->title,
                    'slug' => $learningModule->slug,
                    'progress' => $this->progressService->serialize(
                        $progressMap->get($learningModule->id),
                    ),
                ],
            )->all(),
        ];
    }

    protected function serializeModuleCard(LearningModule $learningModule, Collection $progressMap): array
    {
        return [
            'id' => $learningModule->id,
            'title' => $learningModule->title,
            'slug' => $learningModule->slug,
            'summary' => Str::limit((string) $learningModule->summary, 180),
            'level' => $learningModule->level?->value,
            'level_label' => $learningModule->level?->label(),
            'estimated_minutes' => $learningModule->estimated_minutes,
            'progress' => $this->progressService->serialize($progressMap->get($learningModule->id)),
        ];
    }

    protected function serializeRecentMiniQuizzes(EloquentCollection $attempts): array
    {
        return $attempts->map(
            fn (Attempt $attempt): array => [
                'id' => $attempt->id,
                'learning_module' => $attempt->learningModule?->title,
                'learning_module_slug' => $attempt->learningModule?->slug,
                'score_total' => $attempt->score_total !== null ? (float) $attempt->score_total : null,
                'accuracy' => $attempt->accuracy !== null ? (float) $attempt->accuracy : null,
                'submitted_at' => $attempt->submitted_at?->toIso8601String(),
            ],
        )->all();
    }
}
