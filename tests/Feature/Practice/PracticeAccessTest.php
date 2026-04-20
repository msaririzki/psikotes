<?php

use App\Enums\DifficultyEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\ScoringTypeEnum;
use App\Models\Category;
use App\Models\Question;
use App\Models\Subtest;
use App\Models\User;

test('inactive subtest can not be opened for practice by regular users', function () {
    $category = Category::query()->create([
        'name' => 'Tes Kecerdasan',
        'slug' => 'tes-kecerdasan',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $subtest = Subtest::query()->create([
        'category_id' => $category->id,
        'name' => 'Numerik',
        'slug' => 'numerik',
        'scoring_type' => ScoringTypeEnum::OBJECTIVE,
        'sort_order' => 1,
        'is_active' => false,
    ]);

    $question = Question::query()->create([
        'category_id' => $category->id,
        'subtest_id' => $subtest->id,
        'question_type' => QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE,
        'difficulty' => DifficultyEnum::EASY,
        'question_text' => '2 + 2 = ?',
        'status' => QuestionStatusEnum::PUBLISHED,
    ]);

    $question->options()->createMany([
        ['option_key' => 'A', 'option_text' => '4', 'is_correct' => true, 'sort_order' => 1],
        ['option_key' => 'B', 'option_text' => '5', 'is_correct' => false, 'sort_order' => 2],
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('practice.subtests.show', ['subtest' => $subtest->slug]))
        ->assertForbidden();
});
