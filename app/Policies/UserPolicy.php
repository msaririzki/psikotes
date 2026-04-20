<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function accessAdminArea(User $user): bool
    {
        return $user->isAdmin() && $user->is_active;
    }

    public function viewHistoryCenter(User $user, User $target): bool
    {
        return $user->is_active && $user->is($target);
    }

    public function viewProgressCenter(User $user, User $target): bool
    {
        return $user->is_active && $user->is($target);
    }

    public function viewStudyPlan(User $user, User $target): bool
    {
        return $user->is_active && $user->is($target);
    }
}
