<?php

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use App\Enums\LearningModuleProgressStatusEnum;
use App\Enums\ModuleLevelEnum;
use App\Enums\ScoringTypeEnum;
use App\Models\Attempt;
use App\Models\Category;
use App\Models\LearningModule;
use App\Models\LearningModuleProgress;
use App\Models\StudyGoal;
use App\Models\Subtest;
use App\Models\User;

test('study plan syncs active weekly and monthly goals', function () {
    [$subtest, $module] = createGoalTrackingCatalog('goal-sync', 'Numerik Goal');
    $user = User::factory()->create();

    LearningModuleProgress::query()->create([
        'user_id' => $user->id,
        'learning_module_id' => $module->id,
        'status' => LearningModuleProgressStatusEnum::IN_PROGRESS,
        'started_at' => now()->subDays(10),
        'last_viewed_at' => now()->subDays(8),
    ]);

    Attempt::query()->create([
        'user_id' => $user->id,
        'mode' => AttemptModeEnum::PRACTICE,
        'category_id' => $subtest->category_id,
        'subtest_id' => $subtest->id,
        'status' => AttemptStatusEnum::SUBMITTED,
        'started_at' => now()->subDay()->subMinutes(20),
        'submitted_at' => now()->subDay(),
        'duration_seconds' => 1200,
        'total_questions' => 10,
        'answered_questions' => 10,
        'correct_answers' => 4,
        'wrong_answers' => 4,
        'blank_answers' => 2,
        'score_total' => 40,
        'accuracy' => 40,
    ]);

    $response = $this->actingAs($user)
        ->withHeaders(goalTrackingHeaders())
        ->get(route('study-plan'))
        ->assertOk()
        ->assertJsonPath('component', 'StudyPlan/Index');

    expect(StudyGoal::query()->where('user_id', $user->id)->count())->toBe(2);
    expect(data_get($response->json(), 'props.studyPlan.goal_tracking.active_goals.0.period_type'))->toBe('weekly');
    expect(data_get($response->json(), 'props.studyPlan.goal_tracking.active_goals.1.period_type'))->toBe('monthly');
    expect(data_get($response->json(), 'props.studyPlan.goal_tracking.summary.primary_focus'))->not->toBeNull();
});

test('goal progress reads real activity and dashboard stays synced with current target', function () {
    [$subtest, $module] = createGoalTrackingCatalog('goal-progress', 'Ketelitian Goal');
    $user = User::factory()->create();

    $progress = LearningModuleProgress::query()->create([
        'user_id' => $user->id,
        'learning_module_id' => $module->id,
        'status' => LearningModuleProgressStatusEnum::IN_PROGRESS,
        'started_at' => now()->subDays(6),
        'last_viewed_at' => now()->subDays(6),
    ]);

    $this->actingAs($user)
        ->withHeaders(goalTrackingHeaders())
        ->get(route('study-plan'))
        ->assertOk();

    $progress->forceFill([
        'status' => LearningModuleProgressStatusEnum::COMPLETED,
        'completed_at' => now()->subDay(),
        'last_viewed_at' => now()->subDay(),
    ])->save();

    foreach ([72, 78] as $score) {
        Attempt::query()->create([
            'user_id' => $user->id,
            'mode' => AttemptModeEnum::PRACTICE,
            'category_id' => $subtest->category_id,
            'subtest_id' => $subtest->id,
            'status' => AttemptStatusEnum::SUBMITTED,
            'started_at' => now()->subHours(4),
            'submitted_at' => now()->subHours(3),
            'duration_seconds' => 900,
            'total_questions' => 10,
            'answered_questions' => 10,
            'correct_answers' => 7,
            'wrong_answers' => 2,
            'blank_answers' => 1,
            'score_total' => $score,
            'accuracy' => $score,
        ]);
    }

    $response = $this->actingAs($user)
        ->withHeaders(goalTrackingHeaders())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertJsonPath('component', 'Dashboard');

    $goalTracking = data_get($response->json(), 'props.studyPlan.goal_tracking');
    $weeklyGoal = collect($goalTracking['active_goals'])->firstWhere('period_type', 'weekly');
    $moduleTarget = collect($weeklyGoal['targets'])->firstWhere('label', 'Modul selesai');
    $practiceTarget = collect($weeklyGoal['targets'])->firstWhere('label', 'Practice selesai');

    expect($goalTracking['primary_goal']['period_type'])->toBe('weekly');
    expect($moduleTarget['current'])->toBe(1);
    expect($practiceTarget['current'])->toBe(2);
    expect($weeklyGoal['alignment']['label'])->not->toBe('');
});

function createGoalTrackingCatalog(string $slugPrefix, string $subtestName): array
{
    $category = Category::query()->create([
        'name' => 'Kategori '.$slugPrefix,
        'slug' => 'kategori-'.$slugPrefix,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $subtest = Subtest::query()->create([
        'category_id' => $category->id,
        'name' => $subtestName,
        'slug' => $slugPrefix,
        'description' => 'Subtes goal tracking.',
        'instruction' => 'Instruksi goal tracking.',
        'scoring_type' => ScoringTypeEnum::OBJECTIVE,
        'default_duration_minutes' => 20,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $module = LearningModule::query()->create([
        'subtest_id' => $subtest->id,
        'title' => 'Modul '.$subtestName,
        'slug' => 'modul-'.$slugPrefix,
        'summary' => 'Ringkasan goal tracking.',
        'content' => 'Konten goal tracking.',
        'level' => ModuleLevelEnum::BASIC,
        'estimated_minutes' => 20,
        'is_published' => true,
        'published_at' => now(),
    ]);

    return [$subtest, $module];
}

function goalTrackingHeaders(): array
{
    return [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'X-Inertia-Version' => file_exists(public_path('build/manifest.json'))
            ? hash_file('xxh128', public_path('build/manifest.json'))
            : '',
    ];
}
