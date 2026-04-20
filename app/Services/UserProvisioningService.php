<?php

namespace App\Services;

use App\Enums\LearningLevelEnum;
use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserProvisioningService
{
    /**
     * @param  array{name: string, email: string, password: string}  $attributes
     */
    public function createParticipant(array $attributes): User
    {
        return DB::transaction(function () use ($attributes): User {
            $user = User::create([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => $attributes['password'],
                'role' => UserRoleEnum::USER,
                'is_active' => true,
                'onboarding_completed' => false,
                'email_verified_at' => app()->isLocal() ? now() : null,
            ]);

            $user->profile()->create([
                'target_exam' => 'Psikotes Polri',
                'learning_level' => LearningLevelEnum::BEGINNER,
                'onboarding_answers' => [
                    'source' => 'registration_default',
                ],
            ]);

            return $user;
        });
    }
}
