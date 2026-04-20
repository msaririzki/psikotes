<?php

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use App\Enums\LearningModuleProgressStatusEnum;
use App\Enums\ModuleLevelEnum;
use App\Models\Attempt;
use App\Models\LearningModule;
use App\Models\LearningModuleProgress;
use App\Models\SimulationPackage;
use App\Models\Subtest;
use App\Models\Category;
use App\Models\User;
test('history center requires authentication', function () {
    $this->get(route('history'))
        ->assertRedirect(route('login'));
});

test('history center only shows current user activities', function () {
    [$subtest, $module] = createHistoryCatalog('history-main');
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    LearningModuleProgress::query()->create([
        'user_id' => $user->id,
        'learning_module_id' => $module->id,
        'status' => LearningModuleProgressStatusEnum::COMPLETED,
        'started_at' => now()->subDays(4),
        'completed_at' => now()->subDays(3),
        'last_viewed_at' => now()->subDays(3),
    ]);

    createSubmittedAttempt($user, AttemptModeEnum::MINI_QUIZ, $subtest, $module, 80, now()->subDays(2));
    createSubmittedAttempt($user, AttemptModeEnum::PRACTICE, $subtest, null, 70, now()->subDay());
    createSubmittedSimulationAttempt($user, $subtest, 88, now()->subHours(4));

    LearningModuleProgress::query()->create([
        'user_id' => $otherUser->id,
        'learning_module_id' => $module->id,
        'status' => LearningModuleProgressStatusEnum::IN_PROGRESS,
        'started_at' => now()->subDays(2),
        'last_viewed_at' => now()->subDay(),
    ]);

    createSubmittedAttempt($otherUser, AttemptModeEnum::PRACTICE, $subtest, null, 55, now()->subHours(2));

    $inertiaHeaders = [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'X-Inertia-Version' => file_exists(public_path('build/manifest.json'))
            ? hash_file('xxh128', public_path('build/manifest.json'))
            : '',
    ];

    $this->actingAs($user)
        ->withHeaders($inertiaHeaders)
        ->get(route('history'))
        ->assertOk()
        ->assertJsonPath('component', 'History/Index')
        ->assertJsonPath('props.summary.learn', 1)
        ->assertJsonPath('props.summary.mini_quiz', 1)
        ->assertJsonPath('props.summary.practice', 1)
        ->assertJsonPath('props.summary.simulation', 1)
        ->assertJsonCount(4, 'props.timeline.data')
        ->assertJsonPath('props.timeline.data.0.kind', 'simulation');

    $this->actingAs($user)
        ->withHeaders($inertiaHeaders)
        ->get(route('history', ['type' => 'practice']))
        ->assertOk()
        ->assertJsonPath('component', 'History/Index')
        ->assertJsonPath('props.filters.type', 'practice')
        ->assertJsonCount(1, 'props.timeline.data')
        ->assertJsonPath('props.timeline.data.0.kind', 'practice');
});

function createHistoryCatalog(string $slugPrefix): array
{
    $category = Category::query()->create([
        'name' => 'Kategori '.$slugPrefix,
        'slug' => 'kategori-'.$slugPrefix,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $subtest = Subtest::query()->create([
        'category_id' => $category->id,
        'name' => 'Subtes '.$slugPrefix,
        'slug' => 'subtes-'.$slugPrefix,
        'description' => 'Subtes history center.',
        'instruction' => 'Instruksi.',
        'scoring_type' => 'objective',
        'default_duration_minutes' => 20,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $module = LearningModule::query()->create([
        'subtest_id' => $subtest->id,
        'title' => 'Modul '.$slugPrefix,
        'slug' => 'modul-'.$slugPrefix,
        'summary' => 'Ringkasan modul.',
        'content' => 'Konten modul.',
        'level' => ModuleLevelEnum::BASIC,
        'estimated_minutes' => 20,
        'is_published' => true,
        'published_at' => now(),
    ]);

    return [$subtest, $module];
}

function createSubmittedAttempt(
    User $user,
    AttemptModeEnum $mode,
    Subtest $subtest,
    ?LearningModule $module,
    float $score,
    $submittedAt,
): Attempt {
    return Attempt::query()->create([
        'user_id' => $user->id,
        'mode' => $mode,
        'category_id' => $subtest->category_id,
        'subtest_id' => $subtest->id,
        'learning_module_id' => $module?->id,
        'status' => AttemptStatusEnum::SUBMITTED,
        'started_at' => $submittedAt->copy()->subMinutes(15),
        'submitted_at' => $submittedAt,
        'duration_seconds' => 900,
        'total_questions' => 5,
        'answered_questions' => 5,
        'correct_answers' => (int) round(($score / 100) * 5),
        'wrong_answers' => 5 - (int) round(($score / 100) * 5),
        'blank_answers' => 0,
        'score_total' => $score,
        'accuracy' => $score,
    ]);
}

function createSubmittedSimulationAttempt(User $user, Subtest $subtest, float $score, $submittedAt): Attempt
{
    $simulationPackage = SimulationPackage::query()->create([
        'title' => 'Paket history '.$subtest->slug,
        'slug' => 'paket-history-'.$subtest->slug,
        'duration_minutes' => 60,
        'question_count' => 5,
        'sort_order' => 1,
        'is_published' => true,
        'published_at' => now(),
    ]);

    return Attempt::query()->create([
        'user_id' => $user->id,
        'mode' => AttemptModeEnum::SIMULATION,
        'simulation_package_id' => $simulationPackage->id,
        'status' => AttemptStatusEnum::SUBMITTED,
        'started_at' => $submittedAt->copy()->subMinutes(30),
        'submitted_at' => $submittedAt,
        'duration_seconds' => 1800,
        'total_questions' => 5,
        'answered_questions' => 5,
        'correct_answers' => 4,
        'wrong_answers' => 1,
        'blank_answers' => 0,
        'score_total' => $score,
        'accuracy' => $score,
        'result_summary' => [
            'package_snapshot' => [
                'title' => $simulationPackage->title,
                'slug' => $simulationPackage->slug,
            ],
        ],
    ]);
}
