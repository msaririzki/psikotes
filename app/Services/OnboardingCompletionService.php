<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class OnboardingCompletionService
{
    public function complete(User $user, array $attributes): User
    {
        return DB::transaction(function () use ($user, $attributes): User {
            $user->forceFill([
                'onboarding_completed' => true,
            ])->save();

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'education_level' => $attributes['education_level'] ?? null,
                    'target_exam' => $attributes['target_exam'],
                    'learning_level' => $attributes['learning_level'],
                    'target_daily_minutes' => $attributes['target_daily_minutes'],
                    'preferred_focus' => $attributes['preferred_focus'],
                    'onboarding_answers' => [
                        'source' => 'manual_onboarding',
                        'completed_at' => now()->toIso8601String(),
                    ],
                ],
            );

            return $user->refresh();
        });
    }
}
