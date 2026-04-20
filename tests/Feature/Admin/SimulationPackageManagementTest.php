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

test('admin can view simulation package index and create a draft package', function () {
    $subtest = createSimulationCmsSubtest();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.simulation-packages.store'), [
            'title' => 'Simulasi Draft Akademik',
            'slug' => 'simulasi-draft-akademik',
            'description' => 'Paket draft untuk validasi CMS.',
            'instruction' => 'Instruksi draft.',
            'duration_minutes' => 90,
            'sort_order' => 1,
            'is_published' => false,
            'subtests' => [
                [
                    'subtest_id' => $subtest->id,
                    'question_count' => 5,
                    'sort_order' => 0,
                ],
            ],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('simulation_packages', [
        'slug' => 'simulasi-draft-akademik',
        'is_published' => false,
        'question_count' => 5,
    ]);
});

test('regular user can not manage simulation packages', function () {
    $subtest = createSimulationCmsSubtest('simulation-user-block');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.simulation-packages.store'), [
            'title' => 'Tidak Boleh',
            'slug' => 'tidak-boleh',
            'duration_minutes' => 60,
            'is_published' => false,
            'subtests' => [
                [
                    'subtest_id' => $subtest->id,
                    'question_count' => 5,
                    'sort_order' => 0,
                ],
            ],
        ])
        ->assertForbidden();
});

test('admin can publish simulation package when bank soal is sufficient', function () {
    $subtest = createSimulationCmsSubtest('simulation-publish');
    createPublishedSimulationQuestionsForCms($subtest, 5);

    $simulationPackage = SimulationPackage::query()->create([
        'title' => 'Paket Publish',
        'slug' => 'paket-publish',
        'duration_minutes' => 75,
        'question_count' => 5,
        'sort_order' => 1,
        'is_published' => false,
    ]);

    $simulationPackage->packageSubtests()->create([
        'subtest_id' => $subtest->id,
        'question_count' => 5,
        'sort_order' => 0,
    ]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->patch(route('admin.simulation-packages.publication', $simulationPackage))
        ->assertRedirect();

    $this->assertDatabaseHas('simulation_packages', [
        'id' => $simulationPackage->id,
        'is_published' => true,
    ]);
});

function createSimulationCmsSubtest(string $slug = 'simulation-cms-subtest'): Subtest
{
    $category = Category::query()->create([
        'name' => 'Tes Simulasi CMS '.fake()->unique()->word(),
        'slug' => 'tes-simulasi-cms-'.fake()->unique()->slug(),
        'sort_order' => 1,
        'is_active' => true,
    ]);

    return Subtest::query()->create([
        'category_id' => $category->id,
        'name' => 'Subtes Simulasi CMS '.fake()->unique()->word(),
        'slug' => $slug,
        'description' => 'Subtes CMS simulation.',
        'instruction' => 'Kerjakan dengan teliti.',
        'scoring_type' => ScoringTypeEnum::OBJECTIVE,
        'default_duration_minutes' => 30,
        'sort_order' => 1,
        'is_active' => true,
    ]);
}

function createPublishedSimulationQuestionsForCms(Subtest $subtest, int $totalQuestions): void
{
    foreach (range(1, $totalQuestions) as $index) {
        $question = Question::query()->create([
            'category_id' => $subtest->category_id,
            'subtest_id' => $subtest->id,
            'code' => 'SIM-CMS-'.$index,
            'question_type' => QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE,
            'difficulty' => DifficultyEnum::MEDIUM,
            'question_text' => 'Soal simulasi CMS nomor '.$index,
            'explanation_text' => 'Pembahasan simulasi CMS nomor '.$index,
            'status' => QuestionStatusEnum::PUBLISHED,
        ]);

        $question->options()->createMany([
            ['option_key' => 'A', 'option_text' => 'Opsi A', 'is_correct' => true, 'sort_order' => 1],
            ['option_key' => 'B', 'option_text' => 'Opsi B', 'is_correct' => false, 'sort_order' => 2],
            ['option_key' => 'C', 'option_text' => 'Opsi C', 'is_correct' => false, 'sort_order' => 3],
        ]);
    }
}
