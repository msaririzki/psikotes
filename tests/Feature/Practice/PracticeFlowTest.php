<?php

use App\Enums\AttemptModeEnum;
use App\Enums\DifficultyEnum;
use App\Enums\ModuleLevelEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\ScoringTypeEnum;
use App\Models\Category;
use App\Models\LearningModule;
use App\Models\Question;
use App\Models\Subtest;
use App\Models\User;

test('user can start practice session save answers submit and reopen result', function () {
    [$category, $subtest] = createPracticeCatalog();
    createPracticeQuestions($category, $subtest);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('practice.attempts.store', ['subtest' => $subtest->slug]), [
            'difficulty' => 'all',
            'question_count' => 5,
            'timer_minutes' => 15,
        ])
        ->assertRedirect();

    $attempt = $user->attempts()->latest('id')->first();

    expect($attempt)->not->toBeNull();
    expect($attempt->mode)->toBe(AttemptModeEnum::PRACTICE);

    $attempt->load('attemptQuestions.question.options');

    $orderedQuestions = $attempt->attemptQuestions
        ->sortBy('display_order')
        ->values();

    $firstQuestion = $orderedQuestions[0]->question;
    $secondQuestion = $orderedQuestions[1]->question;

    $correctOption = $firstQuestion->options->firstWhere('is_correct', true);
    $wrongOption = $secondQuestion->options->firstWhere('is_correct', false);

    $answers = $orderedQuestions->mapWithKeys(function ($attemptQuestion, int $index) use ($correctOption, $wrongOption): array {
        return [
            $attemptQuestion->question_id => match ($index) {
                0 => $correctOption->id,
                1 => $wrongOption->id,
                default => null,
            },
        ];
    })->all();

    $this->actingAs($user)
        ->post(route('practice.attempts.answers.store', $attempt), [
            'answers' => $answers,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('attempts', [
        'id' => $attempt->id,
        'status' => 'in_progress',
        'answered_questions' => 2,
        'blank_answers' => 3,
    ]);

    $this->actingAs($user)
        ->post(route('practice.attempts.submit', $attempt), [
            'answers' => $answers,
        ])
        ->assertRedirect(route('practice.attempts.result', $attempt));

    $attempt->refresh();

    $this->assertDatabaseHas('attempts', [
        'id' => $attempt->id,
        'mode' => 'practice',
        'status' => 'submitted',
        'correct_answers' => 1,
        'wrong_answers' => 1,
        'blank_answers' => 3,
    ]);

    $this->assertDatabaseCount('attempt_answers', 2);

    $this->actingAs($user)
        ->get(route('practice.attempts.result', $attempt))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('practice.index'))
        ->assertOk();
});

function createPracticeCatalog(): array
{
    $category = Category::query()->create([
        'name' => 'Tes Kecerdasan',
        'slug' => 'tes-kecerdasan',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $subtest = Subtest::query()->create([
        'category_id' => $category->id,
        'name' => 'Kemampuan Verbal',
        'slug' => 'kemampuan-verbal',
        'description' => 'Subtes untuk melatih akurasi verbal.',
        'instruction' => 'Pilih jawaban yang paling tepat pada setiap soal.',
        'scoring_type' => ScoringTypeEnum::OBJECTIVE,
        'default_duration_minutes' => 20,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    LearningModule::query()->create([
        'subtest_id' => $subtest->id,
        'title' => 'Dasar Kemampuan Verbal',
        'slug' => 'dasar-kemampuan-verbal',
        'summary' => 'Fondasi verbal.',
        'content' => 'Konten verbal.',
        'level' => ModuleLevelEnum::BASIC,
        'is_published' => true,
        'published_at' => now(),
    ]);

    return [$category, $subtest];
}

function createPracticeQuestions(Category $category, Subtest $subtest): void
{
    foreach ([
        DifficultyEnum::EASY,
        DifficultyEnum::EASY,
        DifficultyEnum::MEDIUM,
        DifficultyEnum::MEDIUM,
        DifficultyEnum::HARD,
        DifficultyEnum::HARD,
    ] as $index => $difficulty) {
        $question = Question::query()->create([
            'category_id' => $category->id,
            'subtest_id' => $subtest->id,
            'code' => 'Q-PR-'.($index + 1),
            'question_type' => QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE,
            'difficulty' => $difficulty,
            'question_text' => 'Soal practice verbal nomor '.($index + 1),
            'explanation_text' => 'Pembahasan practice verbal nomor '.($index + 1),
            'status' => QuestionStatusEnum::PUBLISHED,
        ]);

        $question->options()->createMany([
            ['option_key' => 'A', 'option_text' => 'Pilihan A', 'is_correct' => $index % 2 === 0, 'sort_order' => 1],
            ['option_key' => 'B', 'option_text' => 'Pilihan B', 'is_correct' => $index % 2 !== 0, 'sort_order' => 2],
            ['option_key' => 'C', 'option_text' => 'Pilihan C', 'is_correct' => false, 'sort_order' => 3],
        ]);
    }
}
