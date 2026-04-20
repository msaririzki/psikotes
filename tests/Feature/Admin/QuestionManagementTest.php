<?php

use App\Enums\DifficultyEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\ScoringTypeEnum;
use App\Models\Category;
use App\Models\Question;
use App\Models\Subtest;
use App\Models\User;

test('objective question can not be published without valid options', function () {
    $admin = User::factory()->admin()->create();
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
    $question = Question::query()->create([
        'category_id' => $category->id,
        'subtest_id' => $subtest->id,
        'code' => 'Q-1-AAA111',
        'question_type' => QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE,
        'difficulty' => DifficultyEnum::EASY,
        'question_text' => 'Pilih sinonim yang paling tepat.',
        'status' => QuestionStatusEnum::DRAFT,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $this->from(route('admin.questions.index'))
        ->actingAs($admin)
        ->patch(route('admin.questions.status', $question), [
            'status' => QuestionStatusEnum::PUBLISHED->value,
        ])
        ->assertRedirect(route('admin.questions.index'))
        ->assertSessionHasErrors('status');

    $this->assertDatabaseHas('questions', [
        'id' => $question->id,
        'status' => QuestionStatusEnum::DRAFT->value,
    ]);
});

test('objective question can be published after valid options exist', function () {
    $admin = User::factory()->admin()->create();
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
    $question = Question::query()->create([
        'category_id' => $category->id,
        'subtest_id' => $subtest->id,
        'code' => 'Q-1-BBB222',
        'question_type' => QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE,
        'difficulty' => DifficultyEnum::EASY,
        'question_text' => 'Pilih sinonim yang paling tepat.',
        'status' => QuestionStatusEnum::DRAFT,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $question->options()->createMany([
        [
            'option_key' => 'A',
            'option_text' => 'Pilihan A',
            'is_correct' => false,
            'sort_order' => 1,
        ],
        [
            'option_key' => 'B',
            'option_text' => 'Pilihan B',
            'is_correct' => true,
            'sort_order' => 2,
        ],
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.questions.status', $question), [
            'status' => QuestionStatusEnum::PUBLISHED->value,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('questions', [
        'id' => $question->id,
        'status' => QuestionStatusEnum::PUBLISHED->value,
    ]);
});
