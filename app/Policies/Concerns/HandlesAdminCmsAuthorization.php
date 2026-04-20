<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait HandlesAdminCmsAuthorization
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    protected function canManageCms(User $user): bool
    {
        return $user->isAdmin() && $user->is_active;
    }
}
