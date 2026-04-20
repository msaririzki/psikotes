<?php

use App\Enums\AttemptStatusEnum;
use App\Enums\DifficultyEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\ScoringTypeEnum;
use App\Models\Category;
use App\Models\Question;
use App\Models\SimulationPackage;
use App\Models\Subtest;
use App\Models\User;

test('user can not access another users simulation attempt', function () {
    $simulationPackage = createProtectedSimulationPackage('simulation-protected', 2);
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($owner)
        ->post(route('simulations.attempts.store', ['simulationPackage' => $simulationPackage->slug]))
        ->assertRedirect();

    $attempt = $owner->attempts()->latest('id')->first();

    $this->actingAs($otherUser)
        ->get(route('simulations.attempts.show', $attempt))
        ->assertForbidden();

    $this->actingAs($owner)
        ->post(route('simulations.attempts.submit', $attempt), [
            'answers' => [],
            'flags' => [],
        ])
        ->assertRedirect();

    $this->actingAs($otherUser)
        ->get(route('simulations.attempts.result', $attempt))
        ->assertForbidden();
});

test('expired simulation is auto submitted when progress is posted', function () {
    $simulationPackage = createProtectedSimulationPackage('simulation-expired', 1, 10);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('simulations.attempts.store', ['simulationPackage' => $simulationPackage->slug]))
        ->assertRedirect();

    $attempt = $user->attempts()->latest('id')->first();
    $attempt->load('attemptQuestions.question.options');

    $attemptQuestion = $attempt->attemptQuestions->first();
    $correctOption = $attemptQuestion->question->options->firstWhere('is_correct', true);

    $this->travel(11)->minutes();

    $this->actingAs($user)
        ->post(route('simulations.attempts.progress', $attempt), [
            'answers' => [
                $attemptQuestion->question_id => $correctOption->id,
            ],
            'flags' => [
                $attemptQuestion->question_id => false,
            ],
        ])
        ->assertRedirect(route('simulations.attempts.result', $attempt));

    $attempt->refresh();

    expect($attempt->status)->toBe(AttemptStatusEnum::SUBMITTED);
    expect($attempt->answered_questions)->toBe(1);
    expect($attempt->correct_answers)->toBe(1);
});

function createProtectedSimulationPackage(string $slug, int $questionCount, int $durationMinutes = 30): SimulationPackage
{
    $category = Category::query()->create([
        'name' => 'Tes Proteksi '.fake()->unique()->word(),
        'slug' => 'tes-proteksi-'.fake()->unique()->slug(),
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $subtest = Subtest::query()->create([
        'category_id' => $category->id,
        'name' => 'Subtes Proteksi '.fake()->unique()->word(),
        'slug' => $slug.'-subtest',
        'description' => 'Subtes proteksi simulation.',
        'instruction' => 'Kerjakan dengan akurat.',
        'scoring_type' => ScoringTypeEnum::OBJECTIVE,
        'default_duration_minutes' => $durationMinutes,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    foreach (range(1, $questionCount) as $index) {
        $question = Question::query()->create([
            'category_id' => $category->id,
            'subtest_id' => $subtest->id,
            'code' => strtoupper($slug).'-'.$index,
            'question_type' => QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE,
            'difficulty' => DifficultyEnum::EASY,
            'question_text' => 'Soal proteksi nomor '.$index,
            'explanation_text' => 'Pembahasan proteksi nomor '.$index,
            'status' => QuestionStatusEnum::PUBLISHED,
        ]);

        $question->options()->createMany([
            ['option_key' => 'A', 'option_text' => 'Jawaban benar '.$index, 'is_correct' => true, 'sort_order' => 1],
            ['option_key' => 'B', 'option_text' => 'Jawaban salah '.$index, 'is_correct' => false, 'sort_order' => 2],
        ]);
    }

    $simulationPackage = SimulationPackage::query()->create([
        'title' => 'Paket '.$slug,
        'slug' => $slug,
        'description' => 'Paket proteksi simulation.',
        'instruction' => 'Ikuti timer.',
        'duration_minutes' => $durationMinutes,
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

    return $simulationPackage;
}
