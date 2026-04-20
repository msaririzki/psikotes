<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class ReadinessMilestoneCalculationService
{
    public function __construct(
        protected ReadinessProgressEvaluationService $readinessProgressEvaluationService,
    ) {}

    public function forUser(User $user, array $readiness, Collection $tasks): array
    {
        $payload = $this->readinessProgressEvaluationService->forUser($user, $readiness, $tasks);
        $milestones = collect($payload['milestones']);

        return [
            ...$payload,
            'primary_milestone' => $milestones
                ->sortByDesc('progress')
                ->values()
                ->first(),
            'full_simulation' => $milestones->firstWhere('id', 'full_simulation'),
        ];
    }
}
