<?php

namespace Database\Seeders;

use App\Enums\LearningLevelEnum;
use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Super Admin Prikotes',
                'email' => 'superadmin@prikotes.test',
                'role' => UserRoleEnum::SUPER_ADMIN,
                'verified' => true,
            ],
            [
                'name' => 'Admin Konten Prikotes',
                'email' => 'admin@prikotes.test',
                'role' => UserRoleEnum::ADMIN,
                'verified' => true,
            ],
            [
                'name' => 'Peserta Demo',
                'email' => 'peserta@prikotes.test',
                'role' => UserRoleEnum::USER,
                'verified' => true,
            ],
            [
                'name' => 'Peserta Baru',
                'email' => 'userbaru@prikotes.test',
                'role' => UserRoleEnum::USER,
                'verified' => true,
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make('password'),
                    'role' => $account['role'],
                    'is_active' => true,
                    'onboarding_completed' => false,
                    'email_verified_at' => $account['verified'] ? now() : null,
                ],
            );

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'target_exam' => 'Psikotes Polri',
                    'learning_level' => LearningLevelEnum::BEGINNER,
                    'onboarding_answers' => [
                        'seeded' => true,
                    ],
                ],
            );
        }
    }
}
