<?php

namespace App\Policies;

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use App\Models\Attempt;
use App\Models\User;

class AttemptPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function view(User $user, Attempt $attempt): bool
    {
        return $this->ownsAttemptOfMode($user, $attempt, AttemptModeEnum::MINI_QUIZ);
    }

    public function submit(User $user, Attempt $attempt): bool
    {
        return $this->ownsAttemptOfMode($user, $attempt, AttemptModeEnum::MINI_QUIZ)
            && in_array($attempt->status, [
                AttemptStatusEnum::DRAFT,
                AttemptStatusEnum::IN_PROGRESS,
            ], true);
    }

    public function viewResult(User $user, Attempt $attempt): bool
    {
        return $this->ownsAttemptOfMode($user, $attempt, AttemptModeEnum::MINI_QUIZ)
            && $attempt->status === AttemptStatusEnum::SUBMITTED;
    }

    public function viewPractice(User $user, Attempt $attempt): bool
    {
        return $this->ownsAttemptOfMode($user, $attempt, AttemptModeEnum::PRACTICE);
    }

    public function savePracticeAnswer(User $user, Attempt $attempt): bool
    {
        return $this->ownsAttemptOfMode($user, $attempt, AttemptModeEnum::PRACTICE)
            && in_array($attempt->status, [
                AttemptStatusEnum::DRAFT,
                AttemptStatusEnum::IN_PROGRESS,
            ], true);
    }

    public function submitPractice(User $user, Attempt $attempt): bool
    {
        return $this->savePracticeAnswer($user, $attempt);
    }

    public function viewPracticeResult(User $user, Attempt $attempt): bool
    {
        return $this->ownsAttemptOfMode($user, $attempt, AttemptModeEnum::PRACTICE)
            && $attempt->status === AttemptStatusEnum::SUBMITTED;
    }

    public function viewSimulation(User $user, Attempt $attempt): bool
    {
        return $this->ownsAttemptOfMode($user, $attempt, AttemptModeEnum::SIMULATION);
    }

    public function saveSimulationProgress(User $user, Attempt $attempt): bool
    {
        return $this->ownsAttemptOfMode($user, $attempt, AttemptModeEnum::SIMULATION)
            && in_array($attempt->status, [
                AttemptStatusEnum::DRAFT,
                AttemptStatusEnum::IN_PROGRESS,
            ], true);
    }

    public function submitSimulation(User $user, Attempt $attempt): bool
    {
        return $this->saveSimulationProgress($user, $attempt);
    }

    public function viewSimulationResult(User $user, Attempt $attempt): bool
    {
        return $this->ownsAttemptOfMode($user, $attempt, AttemptModeEnum::SIMULATION)
            && $attempt->status === AttemptStatusEnum::SUBMITTED;
    }

    protected function ownsAttemptOfMode(User $user, Attempt $attempt, AttemptModeEnum $mode): bool
    {
        return $user->is_active
            && $attempt->user_id === $user->id
            && $attempt->mode === $mode;
    }
}
