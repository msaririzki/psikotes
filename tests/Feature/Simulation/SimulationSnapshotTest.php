<?php

use App\Enums\DifficultyEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\ScoringTypeEnum;
use App\Models\Category;
use App\Models\Question;
use App\Models\SimulationPackage;
use App\Models\Subtest;
use App\Models\User;
use App\Services\SimulationSessionService;

test('simulation result reads immutable snapshot instead of live cms changes', function () {
    [$simulationPackage, $question] = createSnapshotSimulationFixture();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('simulations.attempts.store', ['simulationPackage' => $simulationPackage->slug]))
        ->assertRedirect();

    $attempt = $user->attempts()->latest('id')->first();
    $attempt->load('attemptQuestions.question.options');

    $attemptQuestion = $attempt->attemptQuestions->first();
    $correctOption = $attemptQuestion->question->options->firstWhere('is_correct', true);

    $originalQuestionText = $question->question_text;
    $originalExplanation = $question->explanation_text;
    $originalCorrectOptionText = $correctOption->option_text;

    $question->forceFill([
        'question_text' => 'Teks soal terbaru dari CMS',
        'explanation_text' => 'Pembahasan terbaru dari CMS',
    ])->save();

    $correctOption->forceFill([
        'option_text' => 'Opsi benar terbaru dari CMS',
    ])->save();

    $this->actingAs($user)
        ->post(route('simulations.attempts.submit', $attempt), [
            'answers' => [
                $question->id => $correctOption->id,
            ],
            'flags' => [
                $question->id => false,
            ],
        ])
        ->assertRedirect(route('simulations.attempts.result', $attempt));

    $payload = app(SimulationSessionService::class)->resultPayload($attempt->refresh());
    $review = $payload['review'][0];

    expect($payload['simulationPackage']['title'])->toBe($simulationPackage->title);
    expect($review['question_text'])->toBe($originalQuestionText);
    expect($review['explanation_text'])->toBe($originalExplanation);
    expect($review['correct_option']['option_text'])->toBe($originalCorrectOptionText);
});

function createSnapshotSimulationFixture(): array
{
    $category = Category::query()->create([
        'name' => 'Tes Snapshot '.fake()->unique()->word(),
        'slug' => 'tes-snapshot-'.fake()->unique()->slug(),
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $subtest = Subtest::query()->create([
        'category_id' => $category->id,
        'name' => 'Subtes Snapshot '.fake()->unique()->word(),
        'slug' => 'snapshot-subtest',
        'description' => 'Subtes snapshot simulation.',
        'instruction' => 'Kerjakan dengan fokus.',
        'scoring_type' => ScoringTypeEnum::OBJECTIVE,
        'default_duration_minutes' => 20,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $question = Question::query()->create([
        'category_id' => $category->id,
        'subtest_id' => $subtest->id,
        'code' => 'SNAP-1',
        'question_type' => QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE,
        'difficulty' => DifficultyEnum::MEDIUM,
        'question_text' => 'Teks soal asli snapshot',
        'explanation_text' => 'Pembahasan asli snapshot',
        'status' => QuestionStatusEnum::PUBLISHED,
    ]);

    $question->options()->createMany([
        ['option_key' => 'A', 'option_text' => 'Opsi benar asli', 'is_correct' => true, 'sort_order' => 1],
        ['option_key' => 'B', 'option_text' => 'Opsi salah asli', 'is_correct' => false, 'sort_order' => 2],
    ]);

    $simulationPackage = SimulationPackage::query()->create([
        'title' => 'Paket Snapshot',
        'slug' => 'paket-snapshot',
        'description' => 'Paket untuk menguji immutable snapshot.',
        'instruction' => 'Ikuti sesi sampai submit.',
        'duration_minutes' => 20,
        'question_count' => 1,
        'sort_order' => 1,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $simulationPackage->packageSubtests()->create([
        'subtest_id' => $subtest->id,
        'question_count' => 1,
        'sort_order' => 0,
    ]);

    return [$simulationPackage, $question];
}
