<?php

namespace App\Http\Controllers;

use App\Enums\LearningLevelEnum;
use App\Http\Requests\Onboarding\CompleteOnboardingRequest;
use App\Services\OnboardingCompletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user()->loadMissing('profile');

        return Inertia::render('Onboarding/Index', [
            'profile' => [
                'education_level' => $user->profile?->education_level,
                'target_exam' => $user->profile?->target_exam ?? 'Psikotes Polri',
                'learning_level' => $user->profile?->learning_level?->value ?? LearningLevelEnum::BEGINNER->value,
                'target_daily_minutes' => $user->profile?->target_daily_minutes,
                'preferred_focus' => $user->profile?->preferred_focus ?? 'balanced',
            ],
            'onboarding' => [
                'completed' => $user->onboarding_completed,
                'learning_levels' => collect(LearningLevelEnum::cases())
                    ->map(fn (LearningLevelEnum $learningLevel): array => [
                        'value' => $learningLevel->value,
                        'label' => $learningLevel->label(),
                    ])->all(),
                'focus_options' => [
                    ['value' => 'balanced', 'label' => 'Seimbang'],
                    ['value' => 'learn', 'label' => 'Fokus materi'],
                    ['value' => 'practice', 'label' => 'Fokus latihan'],
                    ['value' => 'simulation', 'label' => 'Fokus simulasi'],
                ],
            ],
        ]);
    }

    public function store(
        CompleteOnboardingRequest $request,
        OnboardingCompletionService $onboardingCompletionService,
    ): RedirectResponse {
        $onboardingCompletionService->complete($request->user(), $request->validated());

        return to_route('dashboard');
    }
}
