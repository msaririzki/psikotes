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

test('study plan requires authentication', function () {
    $this->get(route('study-plan'))
        ->assertRedirect(route('login'));
});

test('study plan prioritizes foundation work for weak users', function () {
    [$subtest, $module] = createStudyPlanCatalog('weak-plan', 'Numerik Lemah');
    $user = User::factory()->create();

    LearningModuleProgress::query()->create([
        'user_id' => $user->id,
        'learning_module_id' => $module->id,
        'status' => LearningModuleProgressStatusEnum::IN_PROGRESS,
        'started_at' => now()->subDays(15),
        'last_viewed_at' => now()->subDays(12),
    ]);

    Attempt::query()->create([
        'user_id' => $user->id,
        'mode' => AttemptModeEnum::PRACTICE,
        'category_id' => $subtest->category_id,
        'subtest_id' => $subtest->id,
        'status' => AttemptStatusEnum::SUBMITTED,
        'started_at' => now()->subDays(2)->subMinutes(20),
        'submitted_at' => now()->subDays(2),
        'duration_seconds' => 1200,
        'total_questions' => 10,
        'answered_questions' => 10,
        'correct_answers' => 4,
        'wrong_answers' => 4,
        'blank_answers' => 2,
        'score_total' => 40,
        'accuracy' => 40,
    ]);

    $headers = inertiaHeaders();

    $this->actingAs($user)
        ->withHeaders($headers)
        ->get(route('study-plan'))
        ->assertOk()
        ->assertJsonPath('component', 'StudyPlan/Index')
        ->assertJsonPath('props.studyPlan.readiness.state', 'needs_foundation_review')
        ->assertJsonPath('props.studyPlan.next_best_action.track', 'learn')
        ->assertJsonPath('props.studyPlan.next_best_action.action_href', '/learn/modules/'.$module->slug);
});

test('study plan marks strong users ready for full simulation', function () {
    [$verbalSubtest, $verbalModule] = createStudyPlanCatalog('strong-verbal', 'Verbal Kuat');
    [$figuralSubtest, $figuralModule] = createStudyPlanCatalog('strong-figural', 'Figural Kuat');
    $user = User::factory()->create();

    foreach ([$verbalModule, $figuralModule] as $module) {
        LearningModuleProgress::query()->create([
            'user_id' => $user->id,
            'learning_module_id' => $module->id,
            'status' => LearningModuleProgressStatusEnum::COMPLETED,
            'started_at' => now()->subDays(8),
            'completed_at' => now()->subDays(7),
            'last_viewed_at' => now()->subDays(3),
        ]);
    }

    foreach ([
        [$verbalSubtest, 85, now()->subDays(4)],
        [$verbalSubtest, 88, now()->subDays(3)],
        [$figuralSubtest, 90, now()->subDays(2)],
        [$figuralSubtest, 92, now()->subDay()],
    ] as [$subtest, $score, $submittedAt]) {
        Attempt::query()->create([
            'user_id' => $user->id,
            'mode' => AttemptModeEnum::PRACTICE,
            'category_id' => $subtest->category_id,
            'subtest_id' => $subtest->id,
            'status' => AttemptStatusEnum::SUBMITTED,
            'started_at' => $submittedAt->copy()->subMinutes(18),
            'submitted_at' => $submittedAt,
            'duration_seconds' => 1080,
            'total_questions' => 10,
            'answered_questions' => 10,
            'correct_answers' => 9,
            'wrong_answers' => 1,
            'blank_answers' => 0,
            'score_total' => $score,
            'accuracy' => $score,
        ]);
    }

    $headers = inertiaHeaders();

    $this->actingAs($user)
        ->withHeaders($headers)
        ->get(route('study-plan'))
        ->assertOk()
        ->assertJsonPath('component', 'StudyPlan/Index')
        ->assertJsonPath('props.studyPlan.readiness.state', 'ready_for_full_simulation')
        ->assertJsonPath('props.studyPlan.priority_recommendations.0.track', 'simulation');
});

function createStudyPlanCatalog(string $slugPrefix, string $subtestName): array
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
        'description' => 'Subtes adaptive plan.',
        'instruction' => 'Instruksi adaptive plan.',
        'scoring_type' => ScoringTypeEnum::OBJECTIVE,
        'default_duration_minutes' => 20,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $module = LearningModule::query()->create([
        'subtest_id' => $subtest->id,
        'title' => 'Modul '.$subtestName,
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

function inertiaHeaders(): array
{
    return [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'X-Inertia-Version' => file_exists(public_path('build/manifest.json'))
            ? hash_file('xxh128', public_path('build/manifest.json'))
            : '',
    ];
}
