<?php

namespace App\Services;

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use App\Enums\DifficultyEnum;
use App\Enums\PracticeDifficultyFilterEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Models\Attempt;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Subtest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PracticeSessionService
{
    public function __construct(
        protected StudyPlanTaskAutoResolutionService $studyPlanTaskAutoResolutionService,
    ) {}

    public function start(
        User $user,
        Subtest $subtest,
        PracticeDifficultyFilterEnum $difficulty,
        int $questionCount,
        ?int $timerMinutes = null,
    ): Attempt {
        $existingAttempt = Attempt::query()
            ->where('user_id', $user->id)
            ->where('mode', AttemptModeEnum::PRACTICE)
            ->where('subtest_id', $subtest->id)
            ->whereIn('status', [AttemptStatusEnum::DRAFT, AttemptStatusEnum::IN_PROGRESS])
            ->latest('started_at')
            ->first();

        if ($existingAttempt) {
            return $existingAttempt;
        }

        $questions = $this->selectQuestions($subtest, $difficulty, $questionCount);

        return DB::transaction(function () use ($user, $subtest, $difficulty, $questionCount, $timerMinutes, $questions): Attempt {
            $attempt = Attempt::query()->create([
                'user_id' => $user->id,
                'mode' => AttemptModeEnum::PRACTICE,
                'category_id' => $subtest->category_id,
                'subtest_id' => $subtest->id,
                'status' => AttemptStatusEnum::IN_PROGRESS,
                'started_at' => now(),
                'duration_seconds' => 0,
                'total_questions' => $questions->count(),
                'answered_questions' => 0,
                'correct_answers' => 0,
                'wrong_answers' => 0,
                'blank_answers' => $questions->count(),
                'result_summary' => [
                    'configuration' => [
                        'difficulty' => $difficulty->value,
                        'difficulty_label' => $difficulty->label(),
                        'question_count' => $questionCount,
                        'timer_minutes' => $timerMinutes,
                        'timer_enabled' => $timerMinutes !== null,
                    ],
                ],
            ]);

            $attempt->attemptQuestions()->createMany(
                $questions->values()->map(
                    fn (Question $question, int $index): array => [
                        'question_id' => $question->id,
                        'display_order' => $index + 1,
                        'section_name' => 'Practice',
                    ],
                )->all(),
            );

            return $attempt->refresh();
        });
    }

    public function serializeSession(Attempt $attempt): array
    {
        $attempt->load([
            'category:id,name,slug',
            'subtest:id,name,slug',
            'attemptQuestions.question.options' => fn ($optionQuery) => $optionQuery
                ->orderBy('sort_order')
                ->orderBy('option_key'),
            'answers',
        ]);

        $answers = $attempt->answers->keyBy('question_id');
        $configuration = $this->configuration($attempt);
        $remainingSeconds = $this->remainingSeconds($attempt, $configuration['timer_minutes']);

        return [
            'id' => $attempt->id,
            'status' => $attempt->status?->value,
            'started_at' => $attempt->started_at?->toIso8601String(),
            'category' => [
                'name' => $attempt->category?->name,
                'slug' => $attempt->category?->slug,
            ],
            'subtest' => [
                'name' => $attempt->subtest?->name,
                'slug' => $attempt->subtest?->slug,
            ],
            'configuration' => $configuration,
            'progress' => [
                'answered_questions' => $attempt->answered_questions,
                'total_questions' => $attempt->total_questions,
                'remaining_questions' => max($attempt->total_questions - $attempt->answered_questions, 0),
            ],
            'timer' => [
                'enabled' => (bool) $configuration['timer_enabled'],
                'minutes' => $configuration['timer_minutes'],
                'remaining_seconds' => $remainingSeconds,
                'deadline' => $configuration['timer_minutes'] !== null
                    ? $attempt->started_at?->copy()->addMinutes((int) $configuration['timer_minutes'])?->toIso8601String()
                    : null,
            ],
            'questions' => $attempt->attemptQuestions
                ->sortBy('display_order')
                ->values()
                ->map(fn ($attemptQuestion): array => [
                    'id' => $attemptQuestion->question->id,
                    'display_order' => $attemptQuestion->display_order,
                    'code' => $attemptQuestion->question->code,
                    'difficulty' => $attemptQuestion->question->difficulty?->value,
                    'difficulty_label' => $attemptQuestion->question->difficulty?->label(),
                    'question_text' => $attemptQuestion->question->question_text,
                    'options' => $attemptQuestion->question->options->map(
                        fn (QuestionOption $option): array => [
                            'id' => $option->id,
                            'option_key' => $option->option_key,
                            'option_text' => $option->option_text,
                        ],
                    )->values()->all(),
                    'selected_option_id' => $answers->get($attemptQuestion->question->id)?->selected_option_id,
                ])->all(),
        ];
    }

    public function saveAnswers(Attempt $attempt, array $answers): Attempt
    {
        return DB::transaction(function () use ($attempt, $answers): Attempt {
            $attempt->load([
                'attemptQuestions.question.options' => fn ($optionQuery) => $optionQuery
                    ->orderBy('sort_order')
                    ->orderBy('option_key'),
                'answers',
            ]);

            $this->syncAnswers($attempt, $answers);

            return $attempt->refresh();
        });
    }

    public function submit(Attempt $attempt, array $answers): Attempt
    {
        if ($attempt->status === AttemptStatusEnum::SUBMITTED) {
            return $attempt->refresh();
        }

        $attempt = DB::transaction(function () use ($attempt, $answers): Attempt {
            $attempt->load([
                'subtest.learningModules' => fn ($moduleQuery) => $moduleQuery
                    ->where('is_published', true)
                    ->orderBy('published_at')
                    ->orderBy('title'),
                'attemptQuestions.question.options' => fn ($optionQuery) => $optionQuery
                    ->orderBy('sort_order')
                    ->orderBy('option_key'),
                'answers.selectedOption',
            ]);

            $this->syncAnswers($attempt, $answers);

            $attempt->unsetRelation('answers');
            $attempt->load('answers.selectedOption');

            $answersByQuestion = $attempt->answers->keyBy('question_id');

            $correct = 0;
            $wrong = 0;
            $blank = 0;
            $answered = 0;

            foreach ($attempt->attemptQuestions as $attemptQuestion) {
                $question = $attemptQuestion->question;
                $answer = $answersByQuestion->get($question->id);
                $selectedOption = $answer?->selectedOption;
                $isCorrect = $selectedOption ? (bool) $selectedOption->is_correct : null;

                if ($answer && $selectedOption) {
                    $answered++;

                    if ($isCorrect) {
                        $correct++;
                    } else {
                        $wrong++;
                    }
                } else {
                    $blank++;
                }

                if ($answer) {
                    $answer->forceFill([
                        'is_correct' => $isCorrect,
                        'score' => $isCorrect ? 1 : 0,
                    ])->save();
                }
            }

            $totalQuestions = $attempt->attemptQuestions->count();
            $scoreTotal = $totalQuestions > 0
                ? round(($correct / $totalQuestions) * 100, 2)
                : 0;

            $configuration = $this->configuration($attempt);

            $attempt->forceFill([
                'status' => AttemptStatusEnum::SUBMITTED,
                'submitted_at' => now(),
                'duration_seconds' => (int) $attempt->started_at?->diffInSeconds(now()),
                'total_questions' => $totalQuestions,
                'answered_questions' => $answered,
                'correct_answers' => $correct,
                'wrong_answers' => $wrong,
                'blank_answers' => $blank,
                'score_total' => $scoreTotal,
                'accuracy' => $scoreTotal,
                'result_summary' => [
                    'configuration' => $configuration,
                    'recommendation' => $this->recommendationData(
                        $attempt->subtest?->learningModules ?? collect(),
                        $scoreTotal,
                        $configuration,
                    ),
                ],
                'analysis_text' => $this->analysisText($scoreTotal, $correct, $totalQuestions),
            ])->save();

            return $attempt->refresh();
        });

        $this->studyPlanTaskAutoResolutionService->practiceSubmitted($attempt);

        return $attempt;
    }

    public function resultPayload(Attempt $attempt): array
    {
        $attempt->load([
            'category:id,name,slug',
            'subtest.learningModules' => fn ($moduleQuery) => $moduleQuery
                ->where('is_published', true)
                ->orderBy('published_at')
                ->orderBy('title'),
            'attemptQuestions.question.options' => fn ($optionQuery) => $optionQuery
                ->orderBy('sort_order')
                ->orderBy('option_key'),
            'answers.selectedOption',
        ]);

        $configuration = $this->configuration($attempt);
        $recommendation = data_get($attempt->result_summary, 'recommendation')
            ?? $this->recommendationData($attempt->subtest?->learningModules ?? collect(), (float) ($attempt->score_total ?? 0), $configuration);
        $answersByQuestion = $attempt->answers->keyBy('question_id');

        return [
            'attempt' => [
                'id' => $attempt->id,
                'score_total' => $attempt->score_total !== null ? (float) $attempt->score_total : 0,
                'accuracy' => $attempt->accuracy !== null ? (float) $attempt->accuracy : 0,
                'correct_answers' => $attempt->correct_answers,
                'wrong_answers' => $attempt->wrong_answers,
                'blank_answers' => $attempt->blank_answers,
                'answered_questions' => $attempt->answered_questions,
                'total_questions' => $attempt->total_questions,
                'duration_seconds' => $attempt->duration_seconds,
                'submitted_at' => $attempt->submitted_at?->toIso8601String(),
                'analysis_text' => $attempt->analysis_text,
                'configuration' => $configuration,
            ],
            'category' => [
                'name' => $attempt->category?->name,
                'slug' => $attempt->category?->slug,
            ],
            'subtest' => [
                'name' => $attempt->subtest?->name,
                'slug' => $attempt->subtest?->slug,
            ],
            'recommendation' => $recommendation,
            'related_modules' => $attempt->subtest?->learningModules
                ? $attempt->subtest->learningModules->take(3)->map(
                    fn ($module): array => [
                        'title' => $module->title,
                        'slug' => $module->slug,
                    ],
                )->values()->all()
                : [],
            'review' => $attempt->attemptQuestions
                ->sortBy('display_order')
                ->values()
                ->map(function ($attemptQuestion) use ($answersByQuestion): array {
                    $question = $attemptQuestion->question;
                    $answer = $answersByQuestion->get($question->id);
                    $correctOption = $question->options->firstWhere('is_correct', true);

                    return [
                        'id' => $question->id,
                        'display_order' => $attemptQuestion->display_order,
                        'code' => $question->code,
                        'difficulty_label' => $question->difficulty?->label(),
                        'question_text' => $question->question_text,
                        'selected_option' => $answer?->selectedOption
                            ? [
                                'option_key' => $answer->selectedOption->option_key,
                                'option_text' => $answer->selectedOption->option_text,
                            ]
                            : null,
                        'correct_option' => $correctOption
                            ? [
                                'option_key' => $correctOption->option_key,
                                'option_text' => $correctOption->option_text,
                            ]
                            : null,
                        'is_correct' => $answer?->is_correct,
                        'explanation_text' => $question->explanation_text,
                    ];
                })->all(),
        ];
    }

    protected function selectQuestions(
        Subtest $subtest,
        PracticeDifficultyFilterEnum $difficulty,
        int $questionCount,
    ): Collection {
        $questions = $this->eligibleQuestionsQuery($subtest, $difficulty)
            ->with(['options' => fn ($optionQuery) => $optionQuery
                ->orderBy('sort_order')
                ->orderBy('option_key')])
            ->orderBy('id')
            ->get()
            ->filter(fn (Question $question) => $question->canBeUsedForPractice())
            ->shuffle()
            ->values();

        if ($questions->count() < $questionCount) {
            throw ValidationException::withMessages([
                'question_count' => 'Jumlah soal melebihi bank soal published yang valid untuk konfigurasi ini.',
            ]);
        }

        return $questions->take($questionCount)->values();
    }

    protected function syncAnswers(Attempt $attempt, array $answers): void
    {
        $questions = $attempt->attemptQuestions
            ->mapWithKeys(fn ($attemptQuestion): array => [$attemptQuestion->question_id => $attemptQuestion->question]);

        foreach ($answers as $questionId => $selectedOptionId) {
            $question = $questions->get($questionId);

            if (! $question) {
                throw ValidationException::withMessages([
                    'answers.'.$questionId => 'Soal tidak termasuk dalam sesi latihan ini.',
                ]);
            }

            if ($selectedOptionId === null) {
                $attempt->answers()->where('question_id', $questionId)->delete();

                continue;
            }

            $selectedOption = $question->options->firstWhere('id', $selectedOptionId);

            if (! $selectedOption) {
                throw ValidationException::withMessages([
                    'answers.'.$questionId => 'Jawaban yang dipilih tidak valid untuk soal ini.',
                ]);
            }

            $attempt->answers()->updateOrCreate(
                ['question_id' => $questionId],
                [
                    'selected_option_id' => $selectedOption->id,
                    'answered_at' => now(),
                    'is_correct' => null,
                    'score' => null,
                ],
            );
        }

        $answeredCount = $attempt->answers()->whereNotNull('selected_option_id')->count();

        $attempt->forceFill([
            'answered_questions' => $answeredCount,
            'blank_answers' => max($attempt->total_questions - $answeredCount, 0),
            'duration_seconds' => (int) $attempt->started_at?->diffInSeconds(now()),
        ])->save();
    }

    protected function configuration(Attempt $attempt): array
    {
        return [
            'difficulty' => data_get($attempt->result_summary, 'configuration.difficulty', PracticeDifficultyFilterEnum::ALL->value),
            'difficulty_label' => data_get($attempt->result_summary, 'configuration.difficulty_label', PracticeDifficultyFilterEnum::ALL->label()),
            'question_count' => data_get($attempt->result_summary, 'configuration.question_count', $attempt->total_questions),
            'timer_minutes' => data_get($attempt->result_summary, 'configuration.timer_minutes'),
            'timer_enabled' => (bool) data_get($attempt->result_summary, 'configuration.timer_enabled', false),
        ];
    }

    protected function remainingSeconds(Attempt $attempt, ?int $timerMinutes): ?int
    {
        if ($timerMinutes === null || ! $attempt->started_at) {
            return null;
        }

        return max($timerMinutes * 60 - $attempt->started_at->diffInSeconds(now()), 0);
    }

    protected function recommendationData(
        Collection $learningModules,
        float $score,
        array $configuration,
    ): array {
        $firstModule = $learningModules->first();
        $nextDifficulty = match ($configuration['difficulty'] ?? PracticeDifficultyFilterEnum::ALL->value) {
            PracticeDifficultyFilterEnum::EASY->value => PracticeDifficultyFilterEnum::MEDIUM->label(),
            PracticeDifficultyFilterEnum::MEDIUM->value => PracticeDifficultyFilterEnum::HARD->label(),
            default => PracticeDifficultyFilterEnum::MEDIUM->label(),
        };

        if ($score < 60) {
            return [
                'headline' => 'Kembali ke materi dasar subtes ini',
                'description' => 'Skor latihan menunjukkan fondasi masih perlu dirapikan. Review instruksi subtes dan modul pengantar sebelum memulai practice baru.',
                'primary_action' => 'review_learning_modules',
                'related_module' => $firstModule
                    ? [
                        'title' => $firstModule->title,
                        'slug' => $firstModule->slug,
                    ]
                    : null,
            ];
        }

        if ($score < 80) {
            return [
                'headline' => 'Ulangi practice dengan fokus akurasi',
                'description' => 'Dasar konsep sudah cukup, tetapi masih ada ruang untuk merapikan konsistensi. Ulangi latihan dengan jumlah soal serupa atau timer yang lebih stabil.',
                'primary_action' => 'retry_same_subtest',
                'next_difficulty' => $nextDifficulty,
                'related_module' => $firstModule
                    ? [
                        'title' => $firstModule->title,
                        'slug' => $firstModule->slug,
                    ]
                    : null,
            ];
        }

        return [
            'headline' => 'Naikkan tantangan latihan berikutnya',
            'description' => 'Hasil practice sudah kuat. Lanjutkan ke sesi berikutnya dengan difficulty lebih tinggi atau jumlah soal lebih banyak untuk memperdalam ritme kerja.',
            'primary_action' => 'increase_challenge',
            'next_difficulty' => $nextDifficulty,
            'related_module' => $firstModule
                ? [
                    'title' => $firstModule->title,
                    'slug' => $firstModule->slug,
                ]
                : null,
        ];
    }

    protected function analysisText(float $score, int $correct, int $totalQuestions): string
    {
        if ($score < 60) {
            return "Kamu menjawab {$correct} dari {$totalQuestions} soal dengan benar. Fokuskan review pada pola dasar dan pembahasan yang masih salah.";
        }

        if ($score < 80) {
            return "Kamu menjawab {$correct} dari {$totalQuestions} soal dengan benar. Akurasi sudah mulai terbentuk, tetapi perlu latihan ulang agar ritmenya lebih stabil.";
        }

        return "Kamu menjawab {$correct} dari {$totalQuestions} soal dengan benar. Practice ini menunjukkan kesiapan yang baik untuk menaikkan tantangan secara bertahap.";
    }

    protected function eligibleQuestionsQuery(
        Subtest $subtest,
        PracticeDifficultyFilterEnum $difficulty,
    ): Builder {
        return Question::query()
            ->where('subtest_id', $subtest->id)
            ->when(
                $difficulty->questionDifficulty(),
                fn (Builder $questionQuery, DifficultyEnum $questionDifficulty) => $questionQuery->where('difficulty', $questionDifficulty),
            )
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
