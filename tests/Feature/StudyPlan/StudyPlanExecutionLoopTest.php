<?php

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use App\Enums\LearningModuleProgressStatusEnum;
use App\Enums\ModuleLevelEnum;
use App\Enums\ScoringTypeEnum;
use App\Enums\StudyPlanTaskStatusEnum;
use App\Models\Attempt;
use App\Models\Category;
use App\Models\LearningModule;
use App\Models\LearningModuleProgress;
use App\Models\StudyPlanTask;
use App\Models\Subtest;
use App\Models\User;

test('study plan syncs executable tasks and agenda', function () {
    [$subtest, $module] = createExecutionLoopCatalog('execution-sync', 'Numerik Eksekusi');
    $user = User::factory()->create();

    LearningModuleProgress::query()->create([
        'user_id' => $user->id,
        'learning_module_id' => $module->id,
        'status' => LearningModuleProgressStatusEnum::IN_PROGRESS,
        'started_at' => now()->subDays(12),
        'last_viewed_at' => now()->subDays(9),
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

    $this->actingAs($user)
        ->withHeaders(studyPlanExecutionHeaders())
        ->get(route('study-plan'))
        ->assertOk()
        ->assertJsonPath('component', 'StudyPlan/Index')
        ->assertJsonPath('props.studyPlan.agenda.today.0.status', 'pending');

    expect(StudyPlanTask::query()->where('user_id', $user->id)->count())->toBeGreaterThan(1);

    $task = StudyPlanTask::query()
        ->where('user_id', $user->id)
        ->where('task_key', 'weak-foundation-'.$subtest->id)
        ->first();

    expect($task)->not->toBeNull();
    expect($task->status)->toBe(StudyPlanTaskStatusEnum::PENDING);
    expect($task->is_active)->toBeTrue();
});

test('user can snooze task and later mark it done', function () {
    [$subtest, $module] = createExecutionLoopCatalog('execution-transition', 'Ketelitian Eksekusi');
    $user = User::factory()->create();

    LearningModuleProgress::query()->create([
        'user_id' => $user->id,
        'learning_module_id' => $module->id,
        'status' => LearningModuleProgressStatusEnum::IN_PROGRESS,
        'started_at' => now()->subDays(14),
        'last_viewed_at' => now()->subDays(10),
    ]);

    Attempt::query()->create([
        'user_id' => $user->id,
        'mode' => AttemptModeEnum::PRACTICE,
        'category_id' => $subtest->category_id,
        'subtest_id' => $subtest->id,
        'status' => AttemptStatusEnum::SUBMITTED,
        'started_at' => now()->subDays(2)->subMinutes(18),
        'submitted_at' => now()->subDays(2),
        'duration_seconds' => 1080,
        'total_questions' => 10,
        'answered_questions' => 10,
        'correct_answers' => 5,
        'wrong_answers' => 3,
        'blank_answers' => 2,
        'score_total' => 50,
        'accuracy' => 50,
    ]);

    $this->actingAs($user)
        ->withHeaders(studyPlanExecutionHeaders())
        ->get(route('study-plan'));

    $task = StudyPlanTask::query()
        ->where('user_id', $user->id)
        ->where('task_key', 'weak-foundation-'.$subtest->id)
        ->firstOrFail();

    $futureDate = now()->addDays(3)->toDateString();

    $this->actingAs($user)
        ->patch(route('study-plan.tasks.update', $task), [
            'action' => 'snooze',
            'scheduled_for' => $futureDate,
            'redirect_to' => '/study-plan',
        ])
        ->assertRedirect('/study-plan');

    expect($task->fresh()->status)->toBe(StudyPlanTaskStatusEnum::SNOOZED);
    expect($task->fresh()->scheduled_for_date?->toDateString())->toBe($futureDate);

    $this->actingAs($user)
        ->patch(route('study-plan.tasks.update', $task), [
            'action' => 'done',
            'redirect_to' => '/study-plan',
        ])
        ->assertRedirect('/study-plan');

    expect($task->fresh()->status)->toBe(StudyPlanTaskStatusEnum::COMPLETED);
    expect($task->fresh()->completed_at)->not->toBeNull();
});

test('dashboard recalculates next best action after task completion', function () {
    [$subtest, $module] = createExecutionLoopCatalog('execution-dashboard', 'Verbal Eksekusi');
    $user = User::factory()->create();

    LearningModuleProgress::query()->create([
        'user_id' => $user->id,
        'learning_module_id' => $module->id,
        'status' => LearningModuleProgressStatusEnum::IN_PROGRESS,
        'started_at' => now()->subDays(11),
        'last_viewed_at' => now()->subDays(8),
    ]);

    Attempt::query()->create([
        'user_id' => $user->id,
        'mode' => AttemptModeEnum::PRACTICE,
        'category_id' => $subtest->category_id,
        'subtest_id' => $subtest->id,
        'status' => AttemptStatusEnum::SUBMITTED,
        'started_at' => now()->subDays(2)->subMinutes(16),
        'submitted_at' => now()->subDays(2),
        'duration_seconds' => 960,
        'total_questions' => 10,
        'answered_questions' => 10,
        'correct_answers' => 4,
        'wrong_answers' => 5,
        'blank_answers' => 1,
        'score_total' => 40,
        'accuracy' => 40,
    ]);

    $response = $this->actingAs($user)
        ->withHeaders(studyPlanExecutionHeaders())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertJsonPath('component', 'Dashboard');

    $initialTaskKey = data_get($response->json(), 'props.studyPlan.next_best_action.id');

    $task = StudyPlanTask::query()
        ->where('user_id', $user->id)
        ->where('task_key', $initialTaskKey)
        ->firstOrFail();

    $this->actingAs($user)
        ->patch(route('study-plan.tasks.update', $task), [
            'action' => 'done',
            'redirect_to' => '/dashboard',
        ])
        ->assertRedirect('/dashboard');

    $updatedResponse = $this->actingAs($user)
        ->withHeaders(studyPlanExecutionHeaders())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertJsonPath('component', 'Dashboard');

    expect(data_get($updatedResponse->json(), 'props.studyPlan.next_best_action.id'))
        ->not->toBe($initialTaskKey);
});

test('user can not update another users study plan task', function () {
    [$subtest, $module] = createExecutionLoopCatalog('execution-access', 'Figural Eksekusi');
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    LearningModuleProgress::query()->create([
        'user_id' => $owner->id,
        'learning_module_id' => $module->id,
        'status' => LearningModuleProgressStatusEnum::IN_PROGRESS,
        'started_at' => now()->subDays(10),
        'last_viewed_at' => now()->subDays(8),
    ]);

    Attempt::query()->create([
        'user_id' => $owner->id,
        'mode' => AttemptModeEnum::PRACTICE,
        'category_id' => $subtest->category_id,
        'subtest_id' => $subtest->id,
        'status' => AttemptStatusEnum::SUBMITTED,
        'started_at' => now()->subDays(2)->subMinutes(18),
        'submitted_at' => now()->subDays(2),
        'duration_seconds' => 1080,
        'total_questions' => 10,
        'answered_questions' => 10,
        'correct_answers' => 4,
        'wrong_answers' => 4,
        'blank_answers' => 2,
        'score_total' => 40,
        'accuracy' => 40,
    ]);

    $this->actingAs($owner)
        ->withHeaders(studyPlanExecutionHeaders())
        ->get(route('study-plan'));

    $task = StudyPlanTask::query()->where('user_id', $owner->id)->firstOrFail();

    $this->actingAs($intruder)
        ->patch(route('study-plan.tasks.update', $task), [
            'action' => 'done',
            'redirect_to' => '/study-plan',
        ])
        ->assertForbidden();
});

function createExecutionLoopCatalog(string $slugPrefix, string $subtestName): array
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
        'description' => 'Subtes execution loop.',
        'instruction' => 'Instruksi execution loop.',
        'scoring_type' => ScoringTypeEnum::OBJECTIVE,
        'default_duration_minutes' => 20,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $module = LearningModule::query()->create([
        'subtest_id' => $subtest->id,
        'title' => 'Modul '.$subtestName,
        'slug' => 'modul-'.$slugPrefix,
        'summary' => 'Ringkasan execution loop.',
        'content' => 'Konten execution loop.',
        'level' => ModuleLevelEnum::BASIC,
        'estimated_minutes' => 20,
        'is_published' => true,
        'published_at' => now(),
    ]);

    return [$subtest, $module];
}

function studyPlanExecutionHeaders(): array
{
    return [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'X-Inertia-Version' => file_exists(public_path('build/manifest.json'))
            ? hash_file('xxh128', public_path('build/manifest.json'))
            : '',
    ];
}
