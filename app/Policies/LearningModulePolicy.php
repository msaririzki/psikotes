<?php

namespace App\Policies;

use App\Models\LearningModule;
use App\Models\User;
use App\Policies\Concerns\HandlesAdminCmsAuthorization;

class LearningModulePolicy
{
    use HandlesAdminCmsAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->canManageCms($user);
    }

    public function view(User $user, LearningModule $learningModule): bool
    {
        return $this->canManageCms($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageCms($user);
    }

    public function update(User $user, LearningModule $learningModule): bool
    {
        return $this->canManageCms($user);
    }

    public function delete(User $user, LearningModule $learningModule): bool
    {
        return $this->canManageCms($user);
    }

    public function restore(User $user, LearningModule $learningModule): bool
    {
        return false;
    }

    public function forceDelete(User $user, LearningModule $learningModule): bool
    {
        return false;
    }

    public function togglePublication(User $user, LearningModule $learningModule): bool
    {
        return $this->canManageCms($user);
    }

    public function viewLearn(User $user, LearningModule $learningModule): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $learningModule->is_published
            && $learningModule->subtest?->is_active
            && $learningModule->subtest?->category?->is_active;
    }

    public function markComplete(User $user, LearningModule $learningModule): bool
    {
        return $this->viewLearn($user, $learningModule);
    }

    public function startMiniQuiz(User $user, LearningModule $learningModule): bool
    {
        return $this->viewLearn($user, $learningModule);
    }
}
