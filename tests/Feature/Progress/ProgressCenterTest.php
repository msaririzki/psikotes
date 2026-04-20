<?php

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use App\Enums\DifficultyEnum;
use App\Enums\LearningModuleProgressStatusEnum;
use App\Enums\ModuleLevelEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Models\Attempt;
use App\Models\Category;
use App\Models\LearningModule;
use App\Models\LearningModuleProgress;
use App\Models\Question;
use App\Models\SimulationPackage;
use App\Models\Subtest;
use App\Models\User;

test('progress center shows unified analytics and recommendations', function () {
    [$verbalSubtest, $verbalModule] = createProgressCatalog('verbal-progress', 'Kemampuan Verbal');
    [$figuralSubtest, $figuralModule] = createProgressCatalog('figural-progress', 'Kemampuan Figural');

    $user = User::factory()->create();

    LearningModuleProgress::query()->create([
        'user_id' => $user->id,
        'learning_module_id' => $verbalModule->id,
        'status' => LearningModuleProgressStatusEnum::COMPLETED,
        'started_at' => now()->subDays(18),
        'completed_at' => now()->subDays(17),
        'last_viewed_at' => now()->subDays(15),
    ]);

    LearningModuleProgress::query()->create([
        'user_id' => $user->id,
        'learning_module_id' => $figuralModule->id,
        'status' => LearningModuleProgressStatusEnum::IN_PROGRESS,
        'started_at' => now()->subDays(5),
        'last_viewed_at' => now()->subDays(2),
    ]);

    createProgressAttempt($user, AttemptModeEnum::MINI_QUIZ, $verbalSubtest, $verbalModule, 45, now()->subDays(6));
    createProgressAttempt($user, AttemptModeEnum::PRACTICE, $verbalSubtest, null, 55, now()->subDays(4));
    createProgressAttempt($user, AttemptModeEnum::PRACTICE, $figuralSubtest, null, 82, now()->subDays(3));
    createSimulationAnalyticsAttempt($user, $figuralSubtest, 90, now()->subDay());

    $inertiaHeaders = [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'X-Inertia-Version' => file_exists(public_path('build/manifest.json'))
            ? hash_file('xxh128', public_path('build/manifest.json'))
            : '',
    ];

    $this->actingAs($user)
        ->withHeaders($inertiaHeaders)
        ->get(route('progress'))
        ->assertOk()
        ->assertJsonPath('component', 'Progress/Index')
        ->assertJsonPath('props.summary.learning_sessions', 2)
        ->assertJsonPath('props.summary.practice_attempts', 2)
        ->assertJsonPath('props.summary.simulation_attempts', 1)
        ->assertJsonCount(4, 'props.trend')
        ->assertJsonCount(2, 'props.subtest_analytics')
        ->assertJsonPath('props.insights.strongest_area.subtest_name', 'Kemampuan Figural')
        ->assertJsonPath('props.insights.weakest_area.subtest_name', 'Kemampuan Verbal');
});

function createProgressCatalog(string $slugPrefix, string $subtestName): array
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
        'description' => 'Subtes progress center.',
        'instruction' => 'Instruksi subtes.',
        'scoring_type' => 'objective',
        'default_duration_minutes' => 25,
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

function createProgressAttempt(
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
        'started_at' => $submittedAt->copy()->subMinutes(12),
        'submitted_at' => $submittedAt,
        'duration_seconds' => 720,
        'total_questions' => 5,
        'answered_questions' => 5,
        'correct_answers' => (int) round(($score / 100) * 5),
        'wrong_answers' => 5 - (int) round(($score / 100) * 5),
        'blank_answers' => 0,
        'score_total' => $score,
        'accuracy' => $score,
    ]);
}

function createSimulationAnalyticsAttempt(User $user, Subtest $subtest, float $score, $submittedAt): Attempt
{
    $simulationPackage = SimulationPackage::query()->create([
        'title' => 'Paket progress '.$subtest->slug,
        'slug' => 'paket-progress-'.$subtest->slug,
        'duration_minutes' => 60,
        'question_count' => 2,
        'sort_order' => 1,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $attempt = Attempt::query()->create([
        'user_id' => $user->id,
        'mode' => AttemptModeEnum::SIMULATION,
        'simulation_package_id' => $simulationPackage->id,
        'status' => AttemptStatusEnum::SUBMITTED,
        'started_at' => $submittedAt->copy()->subMinutes(35),
        'submitted_at' => $submittedAt,
        'duration_seconds' => 2100,
        'total_questions' => 2,
        'answered_questions' => 2,
        'correct_answers' => 2,
        'wrong_answers' => 0,
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

    foreach ([1, 2] as $index) {
        $question = Question::query()->create([
            'category_id' => $subtest->category_id,
            'subtest_id' => $subtest->id,
            'code' => 'SIM-ANA-'.$index,
            'question_type' => QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE,
            'difficulty' => DifficultyEnum::MEDIUM,
            'question_text' => 'Soal analitik simulasi '.$index,
            'explanation_text' => 'Pembahasan analitik simulasi '.$index,
            'status' => QuestionStatusEnum::PUBLISHED,
        ]);

        $question->options()->createMany([
            [
                'option_key' => 'A',
                'option_text' => 'Opsi benar '.$index,
                'is_correct' => true,
                'sort_order' => 1,
            ],
            [
                'option_key' => 'B',
                'option_text' => 'Opsi salah '.$index,
                'is_correct' => false,
                'sort_order' => 2,
            ],
        ]);

        $correctOption = $question->options()->firstWhere('is_correct', true);

        $attempt->attemptQuestions()->create([
            'question_id' => $question->id,
            'display_order' => $index,
            'section_name' => $subtest->name,
            'snapshot' => [
                'question' => [
                    'id' => $question->id,
                    'subtest_id' => $subtest->id,
                    'code' => 'SIM-ANA-'.$index,
                    'question_text' => 'Soal analitik simulasi '.$index,
                    'difficulty_label' => 'Medium',
                ],
                'options' => [
                    [
                        'id' => $correctOption->id,
                        'option_key' => 'A',
                        'option_text' => 'Opsi benar '.$index,
                        'is_correct' => true,
                    ],
                    [
                        'id' => $question->options->firstWhere('is_correct', false)->id,
                        'option_key' => 'B',
                        'option_text' => 'Opsi salah '.$index,
                        'is_correct' => false,
                    ],
                ],
            ],
        ]);

        $attempt->answers()->create([
            'question_id' => $question->id,
            'selected_option_id' => $correctOption->id,
            'answer_json' => [
                'selected_option' => [
                    'id' => $correctOption->id,
                    'option_key' => 'A',
                    'option_text' => 'Opsi benar '.$index,
                    'is_correct' => true,
                ],
            ],
            'is_correct' => true,
            'score' => 1,
            'answered_at' => $submittedAt->copy()->subMinutes(10),
        ]);
    }

    return $attempt;
}
