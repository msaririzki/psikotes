<?php

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use App\Enums\DifficultyEnum;
use App\Enums\LearningModuleProgressStatusEnum;
use App\Enums\ModuleLevelEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\ScoringTypeEnum;
use App\Enums\StudyPlanTaskEventTypeEnum;
use App\Enums\StudyPlanTaskStatusEnum;
use App\Models\Attempt;
use App\Models\Category;
use App\Models\LearningModule;
use App\Models\LearningModuleProgress;
use App\Models\Question;
use App\Models\SimulationPackage;
use App\Models\StudyPlanTask;
use App\Models\Subtest;
use App\Models\User;

test('stale module review task auto resolves when module is viewed again', function () {
    [$subtest, $module] = createAutoResolutionCatalog('auto-module', 'Auto Module');
    $user = User::factory()->create();

    LearningModuleProgress::query()->create([
        'user_id' => $user->id,
        'learning_module_id' => $module->id,
        'status' => LearningModuleProgressStatusEnum::IN_PROGRESS,
        'started_at' => now()->subDays(12),
        'last_viewed_at' => now()->subDays(9),
    ]);

    $this->actingAs($user)
        ->withHeaders(autoResolutionHeaders())
        ->get(route('study-plan'))
        ->assertOk();

    $task = StudyPlanTask::query()
        ->where('user_id', $user->id)
        ->where('task_key', 'review-module-1')
        ->firstOrFail();

    $this->actingAs($user)
        ->get(route('learn.modules.show', ['learningModule' => $module->slug]))
        ->assertOk();

    $task->refresh();

    expect($task->status)->toBe(StudyPlanTaskStatusEnum::COMPLETED);
    expect($task->completion_source)->toBe('auto');
    expect($task->resolved_activity_type)->toBe('learning_module_viewed');
    expect($task->events()->where('event_type', StudyPlanTaskEventTypeEnum::AUTO_RESOLVED)->exists())->toBeTrue();
});

test('practice task auto resolves when related practice is submitted', function () {
    [$category, $subtest, $module] = createAutoResolutionPracticeCatalog();
    createAutoResolutionPracticeQuestions($category, $subtest);
    $user = User::factory()->create();

    LearningModuleProgress::query()->create([
        'user_id' => $user->id,
        'learning_module_id' => $module->id,
        'status' => LearningModuleProgressStatusEnum::COMPLETED,
        'started_at' => now()->subDays(12),
        'completed_at' => now()->subDays(11),
        'last_viewed_at' => now()->subDays(4),
    ]);

    foreach ([78, 72, 74] as $offset => $score) {
        Attempt::query()->create([
            'user_id' => $user->id,
            'mode' => AttemptModeEnum::PRACTICE,
            'category_id' => $category->id,
            'subtest_id' => $subtest->id,
            'status' => AttemptStatusEnum::SUBMITTED,
            'started_at' => now()->subDays(6 - $offset)->subMinutes(18),
            'submitted_at' => now()->subDays(6 - $offset),
            'duration_seconds' => 1080,
            'total_questions' => 10,
            'answered_questions' => 10,
            'correct_answers' => (int) round($score / 10),
            'wrong_answers' => 10 - (int) round($score / 10),
            'blank_answers' => 0,
            'score_total' => $score,
            'accuracy' => $score,
        ]);
    }

    $this->actingAs($user)
        ->withHeaders(autoResolutionHeaders())
        ->get(route('study-plan'))
        ->assertOk();

    $task = StudyPlanTask::query()
        ->where('user_id', $user->id)
        ->where('task_key', 'stagnant-practice-'.$subtest->id)
        ->firstOrFail();

    $this->actingAs($user)
        ->post(route('practice.attempts.store', ['subtest' => $subtest->slug]), [
            'difficulty' => 'all',
            'question_count' => 3,
            'timer_minutes' => 15,
        ])
        ->assertRedirect();

    $attempt = $user->attempts()->latest('id')->firstOrFail();
    $attempt->load('attemptQuestions.question.options');

    $answers = $attempt->attemptQuestions
        ->sortBy('display_order')
        ->mapWithKeys(function ($attemptQuestion): array {
            $correctOption = $attemptQuestion->question->options->firstWhere('is_correct', true);

            return [$attemptQuestion->question_id => $correctOption?->id];
        })
        ->all();

    $this->actingAs($user)
        ->post(route('practice.attempts.submit', $attempt), [
            'answers' => $answers,
        ])
        ->assertRedirect(route('practice.attempts.result', $attempt));

    $task->refresh();

    expect($task->status)->toBe(StudyPlanTaskStatusEnum::COMPLETED);
    expect($task->completion_source)->toBe('auto');
    expect($task->resolved_activity_type)->toBe('practice_submitted');
    expect($task->events()->where('event_type', StudyPlanTaskEventTypeEnum::AUTO_RESOLVED)->exists())->toBeTrue();
});

test('simulation review task auto resolves when simulation result is opened', function () {
    [$simulationPackage, $subtest] = createAutoResolutionSimulationFixture('auto-sim-review');
    $user = User::factory()->create();

    $attempt = Attempt::query()->create([
        'user_id' => $user->id,
        'mode' => AttemptModeEnum::SIMULATION,
        'simulation_package_id' => $simulationPackage->id,
        'status' => AttemptStatusEnum::SUBMITTED,
        'started_at' => now()->subDays(2)->subMinutes(30),
        'submitted_at' => now()->subDays(2),
        'duration_seconds' => 1800,
        'total_questions' => 2,
        'answered_questions' => 2,
        'correct_answers' => 1,
        'wrong_answers' => 1,
        'blank_answers' => 0,
        'score_total' => 50,
        'accuracy' => 50,
        'result_summary' => [
            'package_snapshot' => [
                'id' => $simulationPackage->id,
                'title' => $simulationPackage->title,
                'slug' => $simulationPackage->slug,
                'duration_minutes' => $simulationPackage->duration_minutes,
                'question_count' => $simulationPackage->question_count,
            ],
            'subtest_breakdown' => [
                [
                    'subtest_name' => $subtest->name,
                    'total_questions' => 2,
                    'correct_answers' => 1,
                    'wrong_answers' => 1,
                    'blank_answers' => 0,
                ],
            ],
        ],
    ]);

    $this->actingAs($user)
        ->withHeaders(autoResolutionHeaders())
        ->get(route('study-plan'))
        ->assertOk();

    $task = StudyPlanTask::query()
        ->where('user_id', $user->id)
        ->where('task_key', 'review-simulation-'.$attempt->id)
        ->firstOrFail();

    $this->actingAs($user)
        ->get(route('simulations.attempts.result', $attempt))
        ->assertOk();

    $task->refresh();

    expect($task->status)->toBe(StudyPlanTaskStatusEnum::COMPLETED);
    expect($task->completion_source)->toBe('auto');
    expect($task->resolved_activity_type)->toBe('simulation_result_viewed');
    expect($task->events()->where('event_type', StudyPlanTaskEventTypeEnum::AUTO_RESOLVED)->exists())->toBeTrue();
});

function createAutoResolutionCatalog(string $slugPrefix, string $subtestName): array
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
        'description' => 'Subtes auto resolution.',
        'instruction' => 'Instruksi auto resolution.',
        'scoring_type' => ScoringTypeEnum::OBJECTIVE,
        'default_duration_minutes' => 20,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $module = LearningModule::query()->create([
        'subtest_id' => $subtest->id,
        'title' => 'Modul '.$subtestName,
        'slug' => 'modul-'.$slugPrefix,
        'summary' => 'Ringkasan.',
        'content' => 'Konten.',
        'level' => ModuleLevelEnum::BASIC,
        'estimated_minutes' => 20,
        'is_published' => true,
        'published_at' => now(),
    ]);

    return [$subtest, $module];
}

function createAutoResolutionPracticeCatalog(): array
{
    $category = Category::query()->create([
        'name' => 'Tes Kecerdasan Auto',
        'slug' => 'tes-kecerdasan-auto',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $subtest = Subtest::query()->create([
        'category_id' => $category->id,
        'name' => 'Verbal Auto',
        'slug' => 'verbal-auto',
        'description' => 'Subtes auto practice.',
        'instruction' => 'Instruksi auto practice.',
        'scoring_type' => ScoringTypeEnum::OBJECTIVE,
        'default_duration_minutes' => 20,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $module = LearningModule::query()->create([
        'subtest_id' => $subtest->id,
        'title' => 'Modul Verbal Auto',
        'slug' => 'modul-verbal-auto',
        'summary' => 'Ringkasan verbal auto.',
        'content' => 'Konten verbal auto.',
        'level' => ModuleLevelEnum::BASIC,
        'estimated_minutes' => 20,
        'is_published' => true,
        'published_at' => now(),
    ]);

    return [$category, $subtest, $module];
}

function createAutoResolutionPracticeQuestions(Category $category, Subtest $subtest): void
{
    foreach (range(1, 4) as $index) {
        $question = Question::query()->create([
            'category_id' => $category->id,
            'subtest_id' => $subtest->id,
            'code' => 'AUTO-PR-'.$index,
            'question_type' => QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE,
            'difficulty' => DifficultyEnum::MEDIUM,
            'question_text' => 'Soal auto practice '.$index,
            'explanation_text' => 'Pembahasan auto practice '.$index,
            'status' => QuestionStatusEnum::PUBLISHED,
        ]);

        $question->options()->createMany([
            ['option_key' => 'A', 'option_text' => 'Pilihan A', 'is_correct' => true, 'sort_order' => 1],
            ['option_key' => 'B', 'option_text' => 'Pilihan B', 'is_correct' => false, 'sort_order' => 2],
            ['option_key' => 'C', 'option_text' => 'Pilihan C', 'is_correct' => false, 'sort_order' => 3],
        ]);
    }
}

function createAutoResolutionSimulationFixture(string $slugPrefix): array
{
    $category = Category::query()->create([
        'name' => 'Sim Auto '.fake()->unique()->word(),
        'slug' => 'sim-auto-'.fake()->unique()->slug(),
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $subtest = Subtest::query()->create([
        'category_id' => $category->id,
        'name' => 'Subtes '.$slugPrefix,
        'slug' => $slugPrefix.'-subtest',
        'description' => 'Subtes simulation auto.',
        'instruction' => 'Instruksi simulation auto.',
        'scoring_type' => ScoringTypeEnum::OBJECTIVE,
        'default_duration_minutes' => 20,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    foreach (range(1, 2) as $index) {
        $question = Question::query()->create([
            'category_id' => $category->id,
            'subtest_id' => $subtest->id,
            'code' => strtoupper($slugPrefix).'-'.$index,
            'question_type' => QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE,
            'difficulty' => DifficultyEnum::MEDIUM,
            'question_text' => 'Soal sim auto '.$index,
            'explanation_text' => 'Pembahasan sim auto '.$index,
            'status' => QuestionStatusEnum::PUBLISHED,
        ]);

        $question->options()->createMany([
            ['option_key' => 'A', 'option_text' => 'Pilihan A', 'is_correct' => true, 'sort_order' => 1],
            ['option_key' => 'B', 'option_text' => 'Pilihan B', 'is_correct' => false, 'sort_order' => 2],
        ]);
    }

    $simulationPackage = SimulationPackage::query()->create([
        'title' => 'Paket '.$slugPrefix,
        'slug' => $slugPrefix,
        'description' => 'Paket simulation auto.',
        'instruction' => 'Instruksi paket simulation auto.',
        'duration_minutes' => 30,
        'question_count' => 2,
        'sort_order' => 1,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $simulationPackage->packageSubtests()->create([
        'subtest_id' => $subtest->id,
        'question_count' => 2,
        'sort_order' => 0,
    ]);

    return [$simulationPackage, $subtest];
}

function autoResolutionHeaders(): array
{
    return [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'X-Inertia-Version' => file_exists(public_path('build/manifest.json'))
            ? hash_file('xxh128', public_path('build/manifest.json'))
            : '',
    ];
}
