<?php

use App\Models\Category;
use App\Models\User;

test('admin can view category index', function () {
    Category::query()->create([
        'name' => 'Tes Kecerdasan',
        'slug' => 'tes-kecerdasan',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.categories.index'))
        ->assertOk();
});

test('regular user can not view category index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.categories.index'))
        ->assertForbidden();
});

test('admin can create a category', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.categories.store'), [
            'name' => 'Tes Kecermatan dan Ketelitian',
            'slug' => 'tes-kecermatan-dan-ketelitian',
            'description' => 'Kategori tes fokus dan kecepatan kerja.',
            'sort_order' => 2,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseHas('categories', [
        'slug' => 'tes-kecermatan-dan-ketelitian',
        'is_active' => true,
    ]);
});
