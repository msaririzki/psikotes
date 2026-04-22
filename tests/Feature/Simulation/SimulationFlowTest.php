<?php

use App\Enums\AttemptModeEnum;
use App\Enums\DifficultyEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\ScoringTypeEnum;
use App\Models\Category;
use App\Models\Question;
use App\Models\SimulationPackage;
use App\Models\Subtest;
use App\Models\User;

test('user can start simulation save progress submit and view result', function () {
    [$simulationPackage, $questions] = createSimulationFixture('simulation-flow', 2);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('simulations.attempts.store', ['simulationPackage' => $simulationPackage->slug]))
        ->assertRedirect();

    $attempt = $user->attempts()->latest('id')->first();

    expect($attempt)->not->toBeNull();
    expect($attempt->mode)->toBe(AttemptModeEnum::SIMULATION);
    expect($attempt->simulation_package_id)->toBe($simulationPackage->id);

    $attempt->load('attemptQuestions.question.options');

    expect($attempt->attemptQuestions)->toHaveCount(2);
    expect($attempt->attemptQuestions->every(fn ($row) => ! empty($row->snapshot)))->toBeTrue();

    $orderedQuestions = $attempt->attemptQuestions->sortBy('display_order')->values();
    $firstQuestion = $orderedQuestions[0]->question;
    $secondQuestion = $orderedQuestions[1]->question;

    $firstCorrectOption = $firstQuestion->options->firstWhere('is_correct', true);
    $secondWrongOption = $secondQuestion->options->firstWhere('is_correct', false);

    $this->actingAs($user)
        ->post(route('simulations.attempts.progress', $attempt), [
            'answers' => [
                $firstQuestion->id => $firstCorrectOption->id,
                $secondQuestion->id => null,
            ],
            'flags' => [
                $firstQuestion->id => false,
                $secondQuestion->id => true,
            ],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('attempt_answers', [
        'attempt_id' => $attempt->id,
        'question_id' => $firstQuestion->id,
        'selected_option_id' => $firstCorrectOption->id,
        'is_flagged' => false,
    ]);

    $this->assertDatabaseHas('attempt_answers', [
        'attempt_id' => $attempt->id,
        'question_id' => $secondQuestion->id,
        'selected_option_id' => null,
        'is_flagged' => true,
    ]);

    $this->actingAs($user)
        ->post(route('simulations.attempts.submit', $attempt), [
            'answers' => [
                $firstQuestion->id => $firstCorrectOption->id,
                $secondQuestion->id => $secondWrongOption->id,
            ],
            'flags' => [
                $firstQuestion->id => false,
                $secondQuestion->id => false,
            ],
        ])
        ->assertRedirect(route('simulations.attempts.result', $attempt));

    $attempt->refresh();

    $this->assertDatabaseHas('attempts', [
        'id' => $attempt->id,
        'mode' => 'simulation',
        'status' => 'submitted',
        'correct_answers' => 1,
        'wrong_answers' => 1,
        'blank_answers' => 0,
    ]);

    expect($attempt->result_summary)->toBeArray();
});

test('simulation progress autosave keeps latest selected answer', function () {
    [$simulationPackage] = createSimulationFixture('simulation-autosave', 1);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('simulations.attempts.store', ['simulationPackage' => $simulationPackage->slug]))
        ->assertRedirect();

    $attempt = $user->attempts()->latest('id')->first();
    $attempt->load('attemptQuestions.question.options');

    $attemptQuestion = $attempt->attemptQuestions->first();
    $question = $attemptQuestion->question;
    $wrongOption = $question->options->firstWhere('is_correct', false);
    $correctOption = $question->options->firstWhere('is_correct', true);

    $this->actingAs($user)
        ->postJson(route('simulations.attempts.progress', $attempt), [
            'answers' => [
                $question->id => $wrongOption->id,
            ],
            'flags' => [
                $question->id => false,
            ],
            'silent' => true,
        ])
        ->assertOk()
        ->assertJsonPath('submitted', false);

    $this->assertDatabaseHas('attempt_answers', [
        'attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'selected_option_id' => $wrongOption->id,
        'is_flagged' => false,
    ]);

    $this->actingAs($user)
        ->postJson(route('simulations.attempts.progress', $attempt), [
            'answers' => [
                $question->id => $correctOption->id,
            ],
            'flags' => [
                $question->id => true,
            ],
            'silent' => true,
        ])
        ->assertOk()
        ->assertJsonPath('submitted', false);

    $this->assertDatabaseHas('attempt_answers', [
        'attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'selected_option_id' => $correctOption->id,
        'is_flagged' => true,
    ]);
});

test('simulation prioritizes unseen questions on subsequent attempts', function () {
    [$simulationPackage] = createSimulationFixture('simulation-rotation', 2, 3);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('simulations.attempts.store', ['simulationPackage' => $simulationPackage->slug]))
        ->assertRedirect();

    $firstAttempt = $user->attempts()->latest('id')->first();
    $firstAttempt->load('attemptQuestions.question.options');

    $firstAnswers = $firstAttempt->attemptQuestions->mapWithKeys(function ($attemptQuestion) {
        $correctOption = $attemptQuestion->question->options->firstWhere('is_correct', true);

        return [$attemptQuestion->question_id => $correctOption->id];
    })->all();

    $firstFlags = $firstAttempt->attemptQuestions->mapWithKeys(fn ($attemptQuestion) => [$attemptQuestion->question_id => false])->all();
    $firstQuestionIds = $firstAttempt->attemptQuestions->pluck('question_id');

    $this->actingAs($user)
        ->post(route('simulations.attempts.submit', $firstAttempt), [
            'answers' => $firstAnswers,
            'flags' => $firstFlags,
        ])
        ->assertRedirect(route('simulations.attempts.result', $firstAttempt));

    $this->actingAs($user)
        ->post(route('simulations.attempts.store', ['simulationPackage' => $simulationPackage->slug]))
        ->assertRedirect();

    $secondAttempt = $user->attempts()->latest('id')->first();
    $secondQuestionIds = $secondAttempt->attemptQuestions->pluck('question_id');

    expect($secondAttempt->id)->not->toBe($firstAttempt->id);
    expect($secondQuestionIds->diff($firstQuestionIds))->not->toBeEmpty();
});

function createSimulationFixture(string $slugPrefix, int $questionCount, ?int $availableQuestionCount = null): array
{
    $availableQuestionCount ??= $questionCount;

    $category = Category::query()->create([
        'name' => 'Tes Simulasi '.fake()->unique()->word(),
        'slug' => 'tes-simulasi-'.fake()->unique()->slug(),
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $subtest = Subtest::query()->create([
        'category_id' => $category->id,
        'name' => 'Subtes Simulasi '.fake()->unique()->word(),
        'slug' => $slugPrefix.'-subtest',
        'description' => 'Subtes untuk flow simulasi.',
        'instruction' => 'Kerjakan sebaik mungkin.',
        'scoring_type' => ScoringTypeEnum::OBJECTIVE,
        'default_duration_minutes' => 20,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $questions = collect();

    foreach (range(1, $availableQuestionCount) as $index) {
        $question = Question::query()->create([
            'category_id' => $category->id,
            'subtest_id' => $subtest->id,
            'code' => strtoupper($slugPrefix).'-'.$index,
            'question_type' => QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE,
            'difficulty' => DifficultyEnum::MEDIUM,
            'question_text' => 'Soal simulasi nomor '.$index,
            'explanation_text' => 'Pembahasan simulasi nomor '.$index,
            'status' => QuestionStatusEnum::PUBLISHED,
        ]);

        $question->options()->createMany([
            ['option_key' => 'A', 'option_text' => 'Pilihan A '.$index, 'is_correct' => true, 'sort_order' => 1],
            ['option_key' => 'B', 'option_text' => 'Pilihan B '.$index, 'is_correct' => false, 'sort_order' => 2],
            ['option_key' => 'C', 'option_text' => 'Pilihan C '.$index, 'is_correct' => false, 'sort_order' => 3],
        ]);

        $questions->push($question);
    }

    $simulationPackage = SimulationPackage::query()->create([
        'title' => 'Paket '.$slugPrefix,
        'slug' => $slugPrefix,
        'description' => 'Paket simulasi untuk flow test.',
        'instruction' => 'Ikuti timer penuh.',
        'duration_minutes' => 30,
        'question_count' => $questionCount,
        'sort_order' => 1,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $simulationPackage->packageSubtests()->create([
        'subtest_id' => $subtest->id,
        'question_count' => $questionCount,
        'sort_order' => 0,
    ]);

    return [$simulationPackage, $questions];
}
