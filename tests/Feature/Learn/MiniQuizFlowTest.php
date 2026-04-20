<?php

use App\Enums\DifficultyEnum;
use App\Enums\ModuleLevelEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\ScoringTypeEnum;
use App\Models\Category;
use App\Models\LearningModule;
use App\Models\Question;
use App\Models\Subtest;
use App\Models\User;

test('user can complete a mini quiz and progress is updated', function () {
    [$category, $subtest, $module] = createMiniQuizCatalog();
    [$firstQuestion, $secondQuestion] = createMiniQuizQuestions($category, $subtest);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('learn.mini-quizzes.store', ['learningModule' => $module->slug]))
        ->assertRedirect();

    $attempt = $user->attempts()->latest('id')->first();

    expect($attempt)->not->toBeNull();
    expect($attempt->learning_module_id)->toBe($module->id);
    expect($attempt->mode->value)->toBe('mini_quiz');

    $correctOption = $firstQuestion->options()->where('is_correct', true)->firstOrFail();
    $wrongOption = $secondQuestion->options()->where('is_correct', false)->firstOrFail();

    $this->actingAs($user)
        ->post(route('learn.mini-quizzes.submit', $attempt), [
            'answers' => [
                $firstQuestion->id => $correctOption->id,
                $secondQuestion->id => $wrongOption->id,
            ],
        ])
        ->assertRedirect(route('learn.mini-quizzes.result', $attempt));

    $attempt->refresh();

    $this->assertDatabaseHas('attempts', [
        'id' => $attempt->id,
        'status' => 'submitted',
        'learning_module_id' => $module->id,
        'correct_answers' => 1,
        'wrong_answers' => 1,
        'blank_answers' => 0,
    ]);

    $this->assertDatabaseCount('attempt_answers', 2);
    $this->assertDatabaseHas('learning_module_progress', [
        'user_id' => $user->id,
        'learning_module_id' => $module->id,
        'status' => 'in_progress',
        'last_quiz_attempt_id' => $attempt->id,
        'quiz_attempts_count' => 1,
    ]);
});

test('user can mark learning module as completed', function () {
    [, , $module] = createMiniQuizCatalog();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('learn.modules.complete', ['learningModule' => $module->slug]))
        ->assertRedirect();

    $this->assertDatabaseHas('learning_module_progress', [
        'user_id' => $user->id,
        'learning_module_id' => $module->id,
        'status' => 'completed',
    ]);
});

function createMiniQuizCatalog(): array
{
    $category = Category::query()->create([
        'name' => 'Tes Kecerdasan',
        'slug' => 'tes-kecerdasan',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $subtest = Subtest::query()->create([
        'category_id' => $category->id,
        'name' => 'Kemampuan Verbal',
        'slug' => 'kemampuan-verbal',
        'scoring_type' => ScoringTypeEnum::OBJECTIVE,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $module = LearningModule::query()->create([
        'subtest_id' => $subtest->id,
        'title' => 'Dasar Verbal',
        'slug' => 'dasar-verbal',
        'summary' => 'Pengenalan verbal.',
        'content' => 'Konten utama verbal.',
        'tips' => 'Baca pertanyaan sampai selesai.',
        'tricks' => 'Eliminasi opsi yang paling lemah.',
        'level' => ModuleLevelEnum::BASIC,
        'is_published' => true,
        'published_at' => now(),
    ]);

    return [$category, $subtest, $module];
}

function createMiniQuizQuestions(Category $category, Subtest $subtest): array
{
    $firstQuestion = Question::query()->create([
        'category_id' => $category->id,
        'subtest_id' => $subtest->id,
        'code' => 'Q-VERBAL-1',
        'question_type' => QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE,
        'difficulty' => DifficultyEnum::EASY,
        'question_text' => 'Sinonim kata "abadi" adalah...',
        'status' => QuestionStatusEnum::PUBLISHED,
    ]);

    $firstQuestion->options()->createMany([
        ['option_key' => 'A', 'option_text' => 'sementara', 'is_correct' => false, 'sort_order' => 1],
        ['option_key' => 'B', 'option_text' => 'kekal', 'is_correct' => true, 'sort_order' => 2],
    ]);

    $secondQuestion = Question::query()->create([
        'category_id' => $category->id,
        'subtest_id' => $subtest->id,
        'code' => 'Q-VERBAL-2',
        'question_type' => QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE,
        'difficulty' => DifficultyEnum::EASY,
        'question_text' => 'Antonim kata "naik" adalah...',
        'status' => QuestionStatusEnum::PUBLISHED,
    ]);

    $secondQuestion->options()->createMany([
        ['option_key' => 'A', 'option_text' => 'turun', 'is_correct' => true, 'sort_order' => 1],
        ['option_key' => 'B', 'option_text' => 'tinggi', 'is_correct' => false, 'sort_order' => 2],
    ]);

    return [$firstQuestion, $secondQuestion];
}
