<?php

use App\Enums\LearningLevelEnum;
use App\Models\User;

test('verified user can open onboarding page', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'onboarding_completed' => false,
    ]);

    $this->actingAs($user)
        ->withHeaders(onboardingHeaders())
        ->get(route('onboarding.show'))
        ->assertOk()
        ->assertJsonPath('component', 'Onboarding/Index')
        ->assertJsonPath('props.onboarding.completed', false);
});

test('verified user can complete onboarding', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'onboarding_completed' => false,
    ]);

    $this->actingAs($user)
        ->post(route('onboarding.store'), [
            'education_level' => 'SMA',
            'target_exam' => 'Psikotes Polri',
            'learning_level' => LearningLevelEnum::BEGINNER->value,
            'target_daily_minutes' => 45,
            'preferred_focus' => 'balanced',
        ])
        ->assertRedirect(route('dashboard'));

    expect($user->fresh()->onboarding_completed)->toBeTrue()
        ->and($user->fresh()->profile->target_daily_minutes)->toBe(45)
        ->and($user->fresh()->profile->preferred_focus)->toBe('balanced');
});

function onboardingHeaders(): array
{
    return [
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'X-Inertia-Version' => file_exists(public_path('build/manifest.json'))
            ? hash_file('xxh128', public_path('build/manifest.json'))
            : '',
    ];
}
