<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use App\Policies\Concerns\HandlesAdminCmsAuthorization;

class QuestionPolicy
{
    use HandlesAdminCmsAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->canManageCms($user);
    }

    public function view(User $user, Question $question): bool
    {
        return $this->canManageCms($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageCms($user);
    }

    public function update(User $user, Question $question): bool
    {
        return $this->canManageCms($user);
    }

    public function delete(User $user, Question $question): bool
    {
        return $this->canManageCms($user);
    }

    public function restore(User $user, Question $question): bool
    {
        return false;
    }

    public function forceDelete(User $user, Question $question): bool
    {
        return false;
    }

    public function updateStatus(User $user, Question $question): bool
    {
        return $this->canManageCms($user);
    }
}
