<?php

namespace App\Services;

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Models\Attempt;
use App\Models\Question;
use App\Models\SimulationPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SimulationSessionService
{
    public function __construct(
        protected AttemptSnapshotService $snapshotService,
        protected StudyPlanTaskAutoResolutionService $studyPlanTaskAutoResolutionService,
    ) {}

    public function start(User $user, SimulationPackage $simulationPackage): Attempt
    {
        $existingAttempt = Attempt::query()
            ->where('user_id', $user->id)
            ->where('mode', AttemptModeEnum::SIMULATION)
            ->where('simulation_package_id', $simulationPackage->id)
            ->whereIn('status', [AttemptStatusEnum::DRAFT, AttemptStatusEnum::IN_PROGRESS])
            ->latest('started_at')
            ->first();

        if ($existingAttempt) {
            return $this->autoSubmitIfExpired($existingAttempt);
        }

        $simulationPackage->load([
            'packageSubtests.subtest.category',
        ]);

        $questions = $this->selectQuestions($simulationPackage);

        return DB::transaction(function () use ($user, $simulationPackage, $questions): Attempt {
            $attempt = Attempt::query()->create([
                'user_id' => $user->id,
                'mode' => AttemptModeEnum::SIMULATION,
                'simulation_package_id' => $simulationPackage->id,
                'status' => AttemptStatusEnum::IN_PROGRESS,
                'started_at' => now(),
                'duration_seconds' => 0,
                'total_questions' => $questions->count(),
                'answered_questions' => 0,
                'correct_answers' => 0,
                'wrong_answers' => 0,
                'blank_answers' => $questions->count(),
                'result_summary' => [
                    'package_snapshot' => $this->packageSnapshot($simulationPackage),
                ],
            ]);

            $attempt->attemptQuestions()->createMany(
                $questions->values()->map(
                    fn (array $row, int $index): array => $this->snapshotService->attemptQuestionPayload(
                        $row['question'],
                        $index + 1,
                        $row['section_name'],
                    ),
                )->all(),
            );

            return $attempt->refresh();
        });
    }

    public function autoSubmitIfExpired(Attempt $attempt): Attempt
    {
        if ($attempt->status === AttemptStatusEnum::SUBMITTED) {
            return $attempt;
        }

        if (! $this->hasExpired($attempt)) {
            return $attempt;
        }

        return $this->submitPersisted($attempt);
    }

    public function serializeSession(Attempt $attempt): array
    {
        $attempt = $this->autoSubmitIfExpired($attempt->refresh());

        if ($attempt->status === AttemptStatusEnum::SUBMITTED) {
            return [
                'submitted' => true,
                'attempt_id' => $attempt->id,
            ];
        }

        $attempt->load(['answers']);

        $answers = $attempt->answers->keyBy('question_id');
        $packageSnapshot = data_get($attempt->result_summary, 'package_snapshot', []);
        $remainingSeconds = $this->remainingSeconds($attempt, (int) ($packageSnapshot['duration_minutes'] ?? 0));

        return [
            'submitted' => false,
            'id' => $attempt->id,
            'started_at' => $attempt->started_at?->toIso8601String(),
            'package' => [
                'title' => $packageSnapshot['title'] ?? $attempt->simulationPackage?->title,
                'slug' => $packageSnapshot['slug'] ?? $attempt->simulationPackage?->slug,
                'duration_minutes' => $packageSnapshot['duration_minutes'] ?? null,
                'question_count' => $attempt->total_questions,
            ],
            'timer' => [
                'remaining_seconds' => $remainingSeconds,
                'deadline' => $attempt->started_at?->copy()->addMinutes((int) ($packageSnapshot['duration_minutes'] ?? 0))?->toIso8601String(),
            ],
            'progress' => [
                'answered_questions' => $attempt->answered_questions,
                'total_questions' => $attempt->total_questions,
                'flagged_questions' => $attempt->answers->where('is_flagged', true)->count(),
            ],
            'questions' => $attempt->attemptQuestions
                ->sortBy('display_order')
                ->values()
                ->map(function ($attemptQuestion) use ($answers): array {
                    $snapshotQuestion = $attemptQuestion->snapshotQuestion();
                    $answer = $answers->get($attemptQuestion->question_id);
                    $selectedOption = $answer?->selectedOptionSnapshot()
                        ?? $attemptQuestion->optionSnapshotById($answer?->selected_option_id);

                    return [
                        'id' => $attemptQuestion->question_id,
                        'display_order' => $attemptQuestion->display_order,
                        'section_name' => $attemptQuestion->section_name,
                        'code' => $snapshotQuestion['code'] ?? null,
                        'difficulty_label' => $snapshotQuestion['difficulty_label'] ?? null,
                        'question_text' => $snapshotQuestion['question_text'] ?? '',
                        'options' => collect($attemptQuestion->snapshotOptions())
                            ->map(fn (array $option): array => [
                                'id' => $option['id'],
                                'option_key' => $option['option_key'],
                                'option_text' => $option['option_text'],
                            ])->all(),
                        'selected_option_id' => $selectedOption['id'] ?? null,
                        'is_flagged' => (bool) ($answer?->is_flagged ?? false),
                    ];
                })->all(),
        ];
    }

    public function saveProgress(Attempt $attempt, array $answers, array $flags): Attempt
    {
        $attempt = $attempt->refresh();

        if ($attempt->status === AttemptStatusEnum::SUBMITTED) {
            return $attempt;
        }

        $attempt = $this->persistProgress($attempt, $answers, $flags);

        if ($this->hasExpired($attempt)) {
            return $this->submitPersisted($attempt);
        }

        return $attempt;
    }

    public function submit(Attempt $attempt, array $answers, array $flags): Attempt
    {
        if ($attempt->status === AttemptStatusEnum::SUBMITTED) {
            return $attempt->refresh();
        }

        $attempt = $this->persistProgress($attempt->refresh(), $answers, $flags);

        return $this->submitPersisted($attempt);
    }

    protected function persistProgress(Attempt $attempt, array $answers, array $flags): Attempt
    {
        return DB::transaction(function () use ($attempt, $answers, $flags): Attempt {
            $attempt->load('attemptQuestions', 'answers');

            $attemptQuestions = $attempt->attemptQuestions->keyBy('question_id');

            foreach ($attemptQuestions as $questionId => $attemptQuestion) {
                $selectedOptionId = $answers[$questionId] ?? null;
                $isFlagged = $flags[$questionId] ?? ($attempt->answers->firstWhere('question_id', $questionId)?->is_flagged ?? false);
                $selectedOptionSnapshot = $this->snapshotService->optionSnapshotById(
                    $attemptQuestion->snapshot ?? [],
                    $selectedOptionId,
                );

                if ($selectedOptionId !== null && ! $selectedOptionSnapshot) {
                    throw ValidationException::withMessages([
                        'answers.'.$questionId => 'Jawaban tidak valid untuk soal simulasi ini.',
                    ]);
                }

                if ($selectedOptionId === null && ! $isFlagged) {
                    $attempt->answers()->where('question_id', $questionId)->delete();

                    continue;
                }

                $attempt->answers()->updateOrCreate(
                    ['question_id' => $questionId],
                    [
                        'selected_option_id' => $selectedOptionSnapshot['id'] ?? null,
                        'answer_json' => $selectedOptionSnapshot
                            ? ['selected_option' => $selectedOptionSnapshot]
                            : null,
                        'is_flagged' => $isFlagged,
                        'answered_at' => $selectedOptionSnapshot ? now() : null,
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

            return $attempt->refresh();
        });
    }

    protected function submitPersisted(Attempt $attempt): Attempt
    {
        if ($attempt->status === AttemptStatusEnum::SUBMITTED) {
            return $attempt->refresh();
        }

        $attempt = DB::transaction(function () use ($attempt): Attempt {
            $attempt->load(['attemptQuestions', 'answers']);

            $answersByQuestion = $attempt->answers->keyBy('question_id');
            $subtestBreakdown = [];
            $correct = 0;
            $wrong = 0;
            $blank = 0;
            $answered = 0;

            foreach ($attempt->attemptQuestions as $attemptQuestion) {
                $snapshot = $attemptQuestion->snapshot ?? [];
                $snapshotQuestion = data_get($snapshot, 'question', []);
                $subtestId = $snapshotQuestion['subtest_id'] ?? 0;
                $subtestLabel = $attemptQuestion->section_name ?? 'Simulation';
                $answer = $answersByQuestion->get($attemptQuestion->question_id);
                $selectedOption = $answer?->selectedOptionSnapshot()
                    ?? $this->snapshotService->optionSnapshotById($snapshot, $answer?->selected_option_id);
                $correctOption = $this->snapshotService->correctOptionSnapshot($snapshot);
                $isCorrect = $selectedOption
                    ? (($selectedOption['id'] ?? null) === ($correctOption['id'] ?? null))
                    : null;

                if (! isset($subtestBreakdown[$subtestId])) {
                    $subtestBreakdown[$subtestId] = [
                        'subtest_name' => $subtestLabel,
                        'total_questions' => 0,
                        'correct_answers' => 0,
                        'wrong_answers' => 0,
                        'blank_answers' => 0,
                    ];
                }

                $subtestBreakdown[$subtestId]['total_questions']++;

                if ($selectedOption) {
                    $answered++;

                    if ($isCorrect) {
                        $correct++;
                        $subtestBreakdown[$subtestId]['correct_answers']++;
                    } else {
                        $wrong++;
                        $subtestBreakdown[$subtestId]['wrong_answers']++;
                    }
                } else {
                    $blank++;
                    $subtestBreakdown[$subtestId]['blank_answers']++;
                }

                if ($answer) {
                    $answer->forceFill([
                        'is_correct' => $selectedOption ? $isCorrect : null,
                        'score' => $selectedOption && $isCorrect ? 1 : 0,
                    ])->save();
                }
            }

            $scoreTotal = $attempt->total_questions > 0
                ? round(($correct / $attempt->total_questions) * 100, 2)
                : 0;

            $packageSnapshot = data_get($attempt->result_summary, 'package_snapshot', []);

            $attempt->forceFill([
                'status' => AttemptStatusEnum::SUBMITTED,
                'submitted_at' => now(),
                'duration_seconds' => (int) $attempt->started_at?->diffInSeconds(now()),
                'answered_questions' => $answered,
                'correct_answers' => $correct,
                'wrong_answers' => $wrong,
                'blank_answers' => $blank,
                'score_total' => $scoreTotal,
                'accuracy' => $scoreTotal,
                'result_summary' => [
                    'package_snapshot' => $packageSnapshot,
                    'subtest_breakdown' => collect($subtestBreakdown)->values()->all(),
                    'recommendation' => $this->recommendationData($scoreTotal),
                ],
                'analysis_text' => $this->analysisText($scoreTotal, $correct, $attempt->total_questions),
            ])->save();

            return $attempt->refresh();
        });

        $this->studyPlanTaskAutoResolutionService->simulationSubmitted($attempt);

        return $attempt;
    }

    public function resultPayload(Attempt $attempt): array
    {
        $attempt->load(['attemptQuestions', 'answers']);

        $answersByQuestion = $attempt->answers->keyBy('question_id');
        $packageSnapshot = data_get($attempt->result_summary, 'package_snapshot', []);

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
            ],
            'simulationPackage' => $packageSnapshot,
            'recommendation' => data_get($attempt->result_summary, 'recommendation', $this->recommendationData((float) ($attempt->score_total ?? 0))),
            'subtest_breakdown' => data_get($attempt->result_summary, 'subtest_breakdown', []),
            'review' => $attempt->attemptQuestions
                ->sortBy('display_order')
                ->values()
                ->map(function ($attemptQuestion) use ($answersByQuestion): array {
                    $snapshot = $attemptQuestion->snapshot ?? [];
                    $snapshotQuestion = data_get($snapshot, 'question', []);
                    $answer = $answersByQuestion->get($attemptQuestion->question_id);
                    $selectedOption = $answer?->selectedOptionSnapshot()
                        ?? $this->snapshotService->optionSnapshotById($snapshot, $answer?->selected_option_id);
                    $correctOption = $this->snapshotService->correctOptionSnapshot($snapshot);

                    return [
                        'id' => $attemptQuestion->question_id,
                        'display_order' => $attemptQuestion->display_order,
                        'section_name' => $attemptQuestion->section_name,
                        'code' => $snapshotQuestion['code'] ?? null,
                        'difficulty_label' => $snapshotQuestion['difficulty_label'] ?? null,
                        'question_text' => $snapshotQuestion['question_text'] ?? '',
                        'selected_option' => $selectedOption
                            ? [
                                'option_key' => $selectedOption['option_key'],
                                'option_text' => $selectedOption['option_text'],
                            ]
                            : null,
                        'correct_option' => $correctOption
                            ? [
                                'option_key' => $correctOption['option_key'],
                                'option_text' => $correctOption['option_text'],
                            ]
                            : null,
                        'is_correct' => $answer?->is_correct,
                        'is_flagged' => (bool) ($answer?->is_flagged ?? false),
                        'explanation_text' => $snapshotQuestion['explanation_text'] ?? null,
                    ];
                })->all(),
        ];
    }

    protected function selectQuestions(SimulationPackage $simulationPackage): Collection
    {
        $questionRows = collect();

        foreach ($simulationPackage->packageSubtests->sortBy('sort_order') as $packageSubtest) {
            $subtest = $packageSubtest->subtest;

            if (! $subtest?->is_active || ! $subtest->category?->is_active) {
                throw ValidationException::withMessages([
                    'simulation_package' => 'Paket simulasi tidak valid karena ada subtes yang nonaktif.',
                ]);
            }

            $eligibleQuestions = Question::query()
                ->with(['options' => fn ($query) => $query->orderBy('sort_order')->orderBy('option_key')])
                ->where('subtest_id', $subtest->id)
                ->where('status', QuestionStatusEnum::PUBLISHED)
                ->whereIn('question_type', [
                    QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE,
                    QuestionTypeEnum::MULTIPLE_CHOICE_IMAGE,
                    QuestionTypeEnum::TRUE_FALSE,
                ])
                ->has('options', '>=', 2)
                ->whereHas('options', fn (Builder $query) => $query->where('is_correct', true))
                ->orderBy('id')
                ->get()
                ->filter(fn (Question $question) => $question->canBeUsedForPractice())
                ->shuffle()
                ->values();

            if ($eligibleQuestions->count() < $packageSubtest->question_count) {
                throw ValidationException::withMessages([
                    'simulation_package' => "Paket simulasi belum bisa dimulai karena soal publish untuk {$subtest->name} belum mencukupi.",
                ]);
            }

            $questionRows = $questionRows->merge(
                $eligibleQuestions->take($packageSubtest->question_count)->map(
                    fn (Question $question): array => [
                        'question' => $question,
                        'section_name' => $subtest->name,
                    ],
                ),
            );
        }

        return $questionRows->shuffle()->values();
    }

    protected function hasExpired(Attempt $attempt): bool
    {
        $durationMinutes = (int) data_get($attempt->result_summary, 'package_snapshot.duration_minutes', 0);

        if ($durationMinutes <= 0 || ! $attempt->started_at) {
            return false;
        }

        return now()->greaterThanOrEqualTo($attempt->started_at->copy()->addMinutes($durationMinutes));
    }

    protected function remainingSeconds(Attempt $attempt, int $durationMinutes): int
    {
        $elapsedSeconds = $attempt->started_at
            ? $attempt->started_at->diffInSeconds(now())
            : 0;

        return max(($durationMinutes * 60) - $elapsedSeconds, 0);
    }

    protected function packageSnapshot(SimulationPackage $simulationPackage): array
    {
        return [
            'id' => $simulationPackage->id,
            'title' => $simulationPackage->title,
            'slug' => $simulationPackage->slug,
            'duration_minutes' => $simulationPackage->duration_minutes,
            'question_count' => $simulationPackage->question_count,
        ];
    }

    protected function recommendationData(float $score): array
    {
        if ($score < 60) {
            return [
                'headline' => 'Perlu review intensif sebelum simulasi berikutnya',
                'description' => 'Fokuskan review ke subtes dengan jawaban salah dan kosong paling tinggi sebelum mencoba paket simulasi lain.',
            ];
        }

        if ($score < 80) {
            return [
                'headline' => 'Fondasi sudah terbentuk, lanjutkan dengan penguatan ritme',
                'description' => 'Hasil simulasi cukup stabil. Review pembahasan dan ulangi paket ini atau practice subtes terlemah untuk merapikan akurasi.',
            ];
        }

        return [
            'headline' => 'Simulasi kuat, naikkan intensitas latihan',
            'description' => 'Skor simulasi sudah baik. Lanjutkan ke paket lain atau ulangi dengan target waktu yang lebih ketat.',
        ];
    }

    protected function analysisText(float $score, int $correct, int $totalQuestions): string
    {
        if ($score < 60) {
            return "Kamu menjawab {$correct} dari {$totalQuestions} soal dengan benar. Review hasil simulasi ini sebelum lanjut ke paket berikutnya.";
        }

        if ($score < 80) {
            return "Kamu menjawab {$correct} dari {$totalQuestions} soal dengan benar. Ritme kerja sudah terbentuk, tetapi masih perlu penguatan di beberapa subtes.";
        }

        return "Kamu menjawab {$correct} dari {$totalQuestions} soal dengan benar. Simulasi ini menunjukkan performa yang kuat dan siap dinaikkan bertahap.";
    }
}
