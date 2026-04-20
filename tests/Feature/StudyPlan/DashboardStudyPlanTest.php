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
use App\Models\Subtest;
use App\Models\User;

test('dashboard exposes study plan snapshot and next best action', function () {
    $category = Category::query()->create([
        'name' => 'Kategori dashboard-study-plan',
        'slug' => 'kategori-dashboard-study-plan',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $subtest = Subtest::query()->create([
        'category_id' => $category->id,
        'name' => 'Ketelitian Dasar',
        'slug' => 'ketelitian-dasar',
        'description' => 'Subtes untuk dashboard adaptive plan.',
        'instruction' => 'Instruksi.',
        'scoring_type' => ScoringTypeEnum::OBJECTIVE,
        'default_duration_minutes' => 20,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $module = LearningModule::query()->create([
        'subtest_id' => $subtest->id,
        'title' => 'Modul Ketelitian Dasar',
        'slug' => 'modul-ketelitian-dasar',
        'summary' => 'Ringkasan.',
        'content' => 'Konten.',
        'level' => ModuleLevelEnum::BASIC,
        'estimated_minutes' => 25,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $user = User::factory()->create();

    LearningModuleProgress::query()->create([
        'user_id' => $user->id,
        'learning_module_id' => $module->id,
        'status' => LearningModuleProgressStatusEnum::IN_PROGRESS,
        'started_at' => now()->subDays(9),
        'last_viewed_at' => now()->subDays(9),
    ]);

    Attempt::query()->create([
        'user_id' => $user->id,
        'mode' => AttemptModeEnum::PRACTICE,
        'category_id' => $subtest->category_id,
        'subtest_id' => $subtest->id,
        'status' => AttemptStatusEnum::SUBMITTED,
        'started_at' => now()->subDay()->subMinutes(15),
        'submitted_at' => now()->subDay(),
        'duration_seconds' => 900,
        'total_questions' => 10,
        'answered_questions' => 10,
        'correct_answers' => 5,
        'wrong_answers' => 3,
        'blank_answers' => 2,
        'score_total' => 50,
        'accuracy' => 50,
    ]);

    $this->actingAs($user)
        ->withHeaders(dashboardInertiaHeaders())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertJsonPath('component', 'Dashboard')
        ->assertJsonPath('props.studyPlan.readiness.state', 'needs_foundation_review')
        ->assertJsonPath('props.studyPlan.next_best_action.track', 'learn');
});

function dashboardInertiaHeaders(): array
{
    return [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'X-Inertia-Version' => file_exists(public_path('build/manifest.json'))
            ? hash_file('xxh128', public_path('build/manifest.json'))
            : '',
    ];
}
