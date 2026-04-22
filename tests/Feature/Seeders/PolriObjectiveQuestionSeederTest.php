<?php

use Database\Seeders\DomainReferenceSeeder;
use Database\Seeders\PolriObjectiveQuestionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('polri objective question seeder imports 445 questions from pdf dataset', function () {
    $this->seed(DomainReferenceSeeder::class);
    $this->seed(PolriObjectiveQuestionSeeder::class);

    expect(\App\Models\Question::count())->toBe(445);
    expect(\App\Models\Question::query()->whereHas('subtest', fn ($query) => $query->where('slug', 'kemampuan-verbal'))->count())->toBe(255);
    expect(\App\Models\Question::query()->whereHas('subtest', fn ($query) => $query->where('slug', 'numerik'))->count())->toBe(30);
    expect(\App\Models\Question::query()->whereHas('subtest', fn ($query) => $query->where('slug', 'penghafalan-kata'))->count())->toBe(15);
    expect(\App\Models\Question::query()->whereHas('subtest', fn ($query) => $query->where('slug', 'hitung-cepat'))->count())->toBe(65);
    expect(\App\Models\Question::query()->whereHas('subtest', fn ($query) => $query->where('slug', 'figural'))->count())->toBe(40);
    expect(\App\Models\Question::query()->whereHas('subtest', fn ($query) => $query->where('slug', 'angka-hilang-dan-huruf-hilang'))->count())->toBe(40);
});
