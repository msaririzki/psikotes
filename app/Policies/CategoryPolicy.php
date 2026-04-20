<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use App\Policies\Concerns\HandlesAdminCmsAuthorization;

class CategoryPolicy
{
    use HandlesAdminCmsAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->canManageCms($user);
    }

    public function view(User $user, Category $category): bool
    {
        return $this->canManageCms($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageCms($user);
    }

    public function update(User $user, Category $category): bool
    {
        return $this->canManageCms($user);
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->canManageCms($user);
    }

    public function restore(User $user, Category $category): bool
    {
        return false;
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return false;
    }

    public function toggleActivity(User $user, Category $category): bool
    {
        return $this->canManageCms($user);
    }
}
