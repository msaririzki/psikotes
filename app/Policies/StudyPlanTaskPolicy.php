<?php

namespace App\Policies;

use App\Models\StudyPlanTask;
use App\Models\User;

class StudyPlanTaskPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function view(User $user, StudyPlanTask $studyPlanTask): bool
    {
        return $user->is_active && $studyPlanTask->user_id === $user->id;
    }

    public function update(User $user, StudyPlanTask $studyPlanTask): bool
    {
        return $this->view($user, $studyPlanTask);
    }
}
