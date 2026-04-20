<?php

namespace App\Services;

use Illuminate\Support\Collection;

class RecommendationPrioritizationService
{
    public function prioritize(Collection $tasks, array $readiness): array
    {
        $stateBonus = match ($readiness['state'] ?? null) {
            'needs_foundation_review' => [
                'learn' => 15,
                'practice' => 5,
                'simulation' => -15,
                'review' => 10,
            ],
            'ready_for_intermediate_practice' => [
                'learn' => 5,
                'practice' => 15,
                'simulation' => 0,
                'review' => 10,
            ],
            'ready_for_subtest_simulation', 'ready_for_full_simulation' => [
                'learn' => 0,
                'practice' => 8,
                'simulation' => 15,
                'review' => 6,
            ],
            default => [
                'learn' => 0,
                'practice' => 0,
                'simulation' => 0,
                'review' => 0,
            ],
        };

        return $tasks
            ->map(function (array $task) use ($stateBonus): array {
                $finalPriority = (int) ($task['priority_score'] + ($stateBonus[$task['track']] ?? 0));

                return [
                    ...$task,
                    'priority_score' => $finalPriority,
                    'priority_label' => $finalPriority >= 90
                        ? 'Critical focus'
                        : ($finalPriority >= 75 ? 'High priority' : 'Recommended next'),
                ];
            })
            ->sortByDesc('priority_score')
            ->unique('id')
            ->values()
            ->all();
    }
}
