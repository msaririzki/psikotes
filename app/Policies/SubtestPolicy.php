<?php

namespace App\Policies;

use App\Models\Subtest;
use App\Models\User;
use App\Policies\Concerns\HandlesAdminCmsAuthorization;

class SubtestPolicy
{
    use HandlesAdminCmsAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->canManageCms($user);
    }

    public function view(User $user, Subtest $subtest): bool
    {
        return $this->canManageCms($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageCms($user);
    }

    public function update(User $user, Subtest $subtest): bool
    {
        return $this->canManageCms($user);
    }

    public function delete(User $user, Subtest $subtest): bool
    {
        return $this->canManageCms($user);
    }

    public function restore(User $user, Subtest $subtest): bool
    {
        return false;
    }

    public function forceDelete(User $user, Subtest $subtest): bool
    {
        return false;
    }

    public function toggleActivity(User $user, Subtest $subtest): bool
    {
        return $this->canManageCms($user);
    }

    public function viewPractice(User $user, Subtest $subtest): bool
    {
        return $this->canAccessPractice($user, $subtest);
    }

    public function startPractice(User $user, Subtest $subtest): bool
    {
        return $this->canAccessPractice($user, $subtest);
    }

    protected function canAccessPractice(User $user, Subtest $subtest): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $subtest->is_active
            && $subtest->category?->is_active;
    }
}
