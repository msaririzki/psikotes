<?php

namespace App\Services;

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use App\Models\Attempt;
use App\Models\LearningModule;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MiniQuizService
{
    public function __construct(
        protected LearningProgressService $progressService,
    ) {}

    public function startForModule(User $user, LearningModule $learningModule): Attempt
    {
        $existingAttempt = Attempt::query()
            ->where('user_id', $user->id)
            ->where('mode', AttemptModeEnum::MINI_QUIZ)
            ->where('learning_module_id', $learningModule->id)
            ->whereIn('status', [AttemptStatusEnum::DRAFT, AttemptStatusEnum::IN_PROGRESS])
            ->latest('started_at')
            ->first();

        if ($existingAttempt) {
            return $existingAttempt;
        }

        $questions = $this->eligibleQuestions($learningModule);

        if ($questions->isEmpty()) {
            throw ValidationException::withMessages([
                'mini_quiz' => 'Mini quiz belum bisa dimulai karena soal publish yang valid belum tersedia pada subtes ini.',
            ]);
        }

        return DB::transaction(function () use ($user, $learningModule, $questions): Attempt {
            $attempt = Attempt::query()->create([
                'user_id' => $user->id,
                'mode' => AttemptModeEnum::MINI_QUIZ,
                'category_id' => $learningModule->subtest?->category_id,
                'subtest_id' => $learningModule->subtest_id,
                'learning_module_id' => $learningModule->id,
                'status' => AttemptStatusEnum::IN_PROGRESS,
                'started_at' => now(),
                'duration_seconds' => 0,
                'total_questions' => $questions->count(),
                'answered_questions' => 0,
                'correct_answers' => 0,
                'wrong_answers' => 0,
                'blank_answers' => $questions->count(),
            ]);

            $attempt->attemptQuestions()->createMany(
                $questions->values()->map(
                    fn (Question $question, int $index): array => [
                        'question_id' => $question->id,
                        'display_order' => $index + 1,
                        'section_name' => 'Mini Quiz',
                    ],
                )->all(),
            );

            $this->progressService->touchForViewing($user, $learningModule);

            return $attempt->refresh();
        });
    }

    public function serializeAttempt(Attempt $attempt): array
    {
        $attempt->load([
            'learningModule:id,title,slug',
            'subtest:id,name,slug',
            'attemptQuestions.question.options' => fn ($optionQuery) => $optionQuery
                ->orderBy('sort_order')
                ->orderBy('option_key'),
            'answers',
        ]);

        $answers = $attempt->answers->keyBy('question_id');

        return [
            'id' => $attempt->id,
            'started_at' => $attempt->started_at?->toIso8601String(),
            'module' => [
                'title' => $attempt->learningModule?->title,
                'slug' => $attempt->learningModule?->slug,
            ],
            'subtest' => [
                'name' => $attempt->subtest?->name,
                'slug' => $attempt->subtest?->slug,
            ],
            'questions' => $attempt->attemptQuestions
                ->sortBy('display_order')
                ->values()
                ->map(fn ($attemptQuestion): array => [
                    'id' => $attemptQuestion->question->id,
                    'display_order' => $attemptQuestion->display_order,
                    'code' => $attemptQuestion->question->code,
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

    public function submit(Attempt $attempt, array $answers): Attempt
    {
        if ($attempt->status === AttemptStatusEnum::SUBMITTED) {
            return $attempt->refresh();
        }

        $attempt->load([
            'learningModule',
            'attemptQuestions.question.options' => fn ($optionQuery) => $optionQuery
                ->orderBy('sort_order')
                ->orderBy('option_key'),
        ]);

        return DB::transaction(function () use ($attempt, $answers): Attempt {
            $correct = 0;
            $wrong = 0;
            $blank = 0;
            $answered = 0;

            foreach ($attempt->attemptQuestions as $attemptQuestion) {
                $question = $attemptQuestion->question;
                $selectedOptionId = $answers[$question->id] ?? null;
                $selectedOption = $selectedOptionId
                    ? $question->options->firstWhere('id', $selectedOptionId)
                    : null;

                if ($selectedOptionId && ! $selectedOption) {
                    throw ValidationException::withMessages([
                        'answers.'.$question->id => 'Jawaban yang dipilih tidak valid untuk soal ini.',
                    ]);
                }

                $isCorrect = $selectedOption?->is_correct;

                if ($selectedOption) {
                    $answered++;

                    if ($isCorrect) {
                        $correct++;
                    } else {
                        $wrong++;
                    }
                } else {
                    $blank++;
                }

                $attempt->answers()->updateOrCreate(
                    ['question_id' => $question->id],
                    [
                        'selected_option_id' => $selectedOption?->id,
                        'is_correct' => $selectedOption ? (bool) $isCorrect : null,
                        'score' => $selectedOption && $isCorrect ? 1 : 0,
                        'answered_at' => $selectedOption ? now() : null,
                    ],
                );
            }

            $totalQuestions = $attempt->attemptQuestions->count();
            $scoreTotal = $totalQuestions > 0
                ? round(($correct / $totalQuestions) * 100, 2)
                : 0;

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
                    'recommendation' => $this->recommendationData($attempt->learningModule, $scoreTotal),
                ],
                'analysis_text' => $this->analysisText($scoreTotal, $correct, $totalQuestions),
            ])->save();

            $this->progressService->syncQuizCompletion(
                $attempt->user,
                $attempt->learningModule,
                $attempt->refresh(),
            );

            return $attempt->refresh();
        });
    }

    public function resultPayload(Attempt $attempt): array
    {
        $attempt->load([
            'learningModule.subtest.category',
            'attemptQuestions.question.options' => fn ($optionQuery) => $optionQuery
                ->orderBy('sort_order')
                ->orderBy('option_key'),
            'answers.selectedOption',
        ]);

        $answerMap = $attempt->answers->keyBy('question_id');
        $recommendation = $this->recommendationData(
            $attempt->learningModule,
            (float) ($attempt->score_total ?? 0),
        );

        return [
            'attempt' => [
                'id' => $attempt->id,
                'score_total' => $attempt->score_total !== null ? (float) $attempt->score_total : 0,
                'accuracy' => $attempt->accuracy !== null ? (float) $attempt->accuracy : 0,
                'correct_answers' => $attempt->correct_answers,
                'wrong_answers' => $attempt->wrong_answers,
                'blank_answers' => $attempt->blank_answers,
                'submitted_at' => $attempt->submitted_at?->toIso8601String(),
                'analysis_text' => $attempt->analysis_text,
            ],
            'module' => [
                'title' => $attempt->learningModule?->title,
                'slug' => $attempt->learningModule?->slug,
                'subtest' => $attempt->learningModule?->subtest?->name,
                'category' => $attempt->learningModule?->subtest?->category?->name,
            ],
            'recommendation' => $recommendation,
            'answers' => $attempt->attemptQuestions
                ->sortBy('display_order')
                ->values()
                ->map(function ($attemptQuestion) use ($answerMap): array {
                    $question = $attemptQuestion->question;
                    $answer = $answerMap->get($question->id);
                    $correctOption = $question->options->firstWhere('is_correct', true);

                    return [
                        'id' => $question->id,
                        'display_order' => $attemptQuestion->display_order,
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

    protected function eligibleQuestions(LearningModule $learningModule): Collection
    {
        return Question::query()
            ->with(['options' => fn ($optionQuery) => $optionQuery
                ->orderBy('sort_order')
                ->orderBy('option_key')])
            ->where('subtest_id', $learningModule->subtest_id)
            ->where('status', 'published')
            ->orderBy('id')
            ->get()
            ->filter(fn (Question $question) => $question->canBeUsedForMiniQuiz())
            ->take(5)
            ->values();
    }

    protected function recommendationData(?LearningModule $learningModule, float $score): array
    {
        $nextModule = null;

        if ($learningModule) {
            $nextModule = LearningModule::query()
                ->where('subtest_id', $learningModule->subtest_id)
                ->where('is_published', true)
                ->where('id', '>', $learningModule->id)
                ->orderBy('id')
                ->first();
        }

        if ($score < 60) {
            return [
                'headline' => 'Ulangi fondasi modul ini',
                'description' => 'Baca ulang pengenalan, tujuan, cara mengerjakan, tips, dan trik sebelum mencoba mini quiz lagi.',
                'primary_action' => 'review_module',
                'next_module' => null,
            ];
        }

        if ($score < 80) {
            return [
                'headline' => 'Pemahaman dasar sudah terbentuk',
                'description' => 'Kamu sudah paham sebagian besar konsep. Review contoh soal dan ulangi mini quiz untuk menguatkan akurasi.',
                'primary_action' => 'retry_quiz',
                'next_module' => $nextModule
                    ? [
                        'title' => $nextModule->title,
                        'slug' => $nextModule->slug,
                    ]
                    : null,
            ];
        }

        return [
            'headline' => 'Siap lanjut ke langkah berikutnya',
            'description' => 'Nilai mini quiz sudah kuat. Tandai modul ini selesai lalu lanjut ke modul berikutnya atau kembali ke subtes.',
            'primary_action' => 'complete_and_continue',
            'next_module' => $nextModule
                ? [
                    'title' => $nextModule->title,
                    'slug' => $nextModule->slug,
                ]
                : null,
        ];
    }

    protected function analysisText(float $score, int $correct, int $totalQuestions): string
    {
        if ($score < 60) {
            return "Kamu menjawab {$correct} dari {$totalQuestions} soal dengan benar. Fokus dulu pada pemahaman pola dasar sebelum mengulang mini quiz.";
        }

        if ($score < 80) {
            return "Kamu menjawab {$correct} dari {$totalQuestions} soal dengan benar. Dasar konsep sudah ada, tetapi akurasi masih bisa diperkuat lewat review cepat.";
        }

        return "Kamu menjawab {$correct} dari {$totalQuestions} soal dengan benar. Pemahaman modul ini sudah kuat dan siap dibawa ke materi berikutnya.";
    }
}
