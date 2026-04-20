<?php

namespace App\Policies;

use App\Models\QuestionOption;
use App\Models\User;
use App\Policies\Concerns\HandlesAdminCmsAuthorization;

class QuestionOptionPolicy
{
    use HandlesAdminCmsAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->canManageCms($user);
    }

    public function view(User $user, QuestionOption $questionOption): bool
    {
        return $this->canManageCms($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageCms($user);
    }

    public function update(User $user, QuestionOption $questionOption): bool
    {
        return $this->canManageCms($user);
    }

    public function delete(User $user, QuestionOption $questionOption): bool
    {
        return $this->canManageCms($user);
    }

    public function restore(User $user, QuestionOption $questionOption): bool
    {
        return false;
    }

    public function forceDelete(User $user, QuestionOption $questionOption): bool
    {
        return false;
    }
}
