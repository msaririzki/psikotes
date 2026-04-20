<?php

use App\Enums\ModuleLevelEnum;
use App\Enums\ScoringTypeEnum;
use App\Models\Category;
use App\Models\LearningModule;
use App\Models\Subtest;
use App\Models\User;

test('published learning module can be viewed by regular users', function () {
    [$category, $subtest] = createLearnCatalog();
    $module = LearningModule::query()->create([
        'subtest_id' => $subtest->id,
        'title' => 'Dasar Verbal',
        'slug' => 'dasar-verbal',
        'summary' => 'Pengenalan verbal.',
        'content' => 'Konten utama verbal.',
        'level' => ModuleLevelEnum::BASIC,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('learn.modules.show', ['learningModule' => $module->slug]))
        ->assertOk();
});

test('unpublished learning module can not be viewed by regular users', function () {
    [, $subtest] = createLearnCatalog();
    $module = LearningModule::query()->create([
        'subtest_id' => $subtest->id,
        'title' => 'Draft Verbal',
        'slug' => 'draft-verbal',
        'summary' => 'Draft.',
        'content' => 'Draft content.',
        'level' => ModuleLevelEnum::BASIC,
        'is_published' => false,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('learn.modules.show', ['learningModule' => $module->slug]))
        ->assertForbidden();
});

function createLearnCatalog(): array
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

    return [$category, $subtest];
}
