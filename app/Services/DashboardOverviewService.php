<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\Category;
use App\Models\LearningModule;
use App\Models\Question;
use App\Models\Subtest;
use App\Models\User;

class DashboardOverviewService
{
    public function publicStats(): array
    {
        return [
            'categories' => Category::query()->count(),
            'subtests' => Subtest::query()->count(),
            'modules' => LearningModule::query()->count(),
            'questions' => Question::query()->count(),
        ];
    }

    public function forUser(User $user): array
    {
        $latestAttempt = $user->attempts()->latest('started_at')->first();

        return [
            'user' => [
                'name' => $user->name,
                'role_label' => $user->role?->label(),
                'onboarding_completed' => $user->onboarding_completed,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            ],
            'catalog' => [
                'categories' => Category::query()->count(),
                'subtests' => Subtest::query()->count(),
                'modules' => LearningModule::query()->count(),
                'questions' => Question::query()->count(),
            ],
            'progress' => [
                'attempts' => $user->attempts()->count(),
                'latest_attempt_started_at' => $latestAttempt?->started_at?->toIso8601String(),
                'latest_attempt_mode' => $latestAttempt?->mode?->value,
            ],
        ];
    }

    public function forAdmin(): array
    {
        return [
            'users' => [
                'total' => User::query()->count(),
                'verified' => User::query()->whereNotNull('email_verified_at')->count(),
                'admins' => User::query()->whereIn('role', ['admin', 'super_admin'])->count(),
            ],
            'content' => [
                'categories' => Category::query()->count(),
                'subtests' => Subtest::query()->count(),
                'modules' => LearningModule::query()->count(),
                'questions' => Question::query()->count(),
            ],
            'activity' => [
                'attempts' => Attempt::query()->count(),
            ],
        ];
    }
}
