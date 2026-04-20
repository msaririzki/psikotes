<?php

namespace App\Services;

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use App\Enums\StudyPlanTaskStatusEnum;
use App\Models\Attempt;
use App\Models\LearningModuleProgress;
use App\Models\StudyPlanTask;
use App\Models\User;
use Illuminate\Support\Collection;

class ReadinessProgressEvaluationService
{
    public function __construct(
        protected SubtestAnalyticsService $subtestAnalyticsService,
    ) {}

    public function forUser(User $user, array $readiness, Collection $tasks): array
    {
        $analyticsPayload = $this->subtestAnalyticsService->forUser($user);
        $subtestAnalytics = collect($analyticsPayload['items']);

        $moduleProgresses = LearningModuleProgress::query()
            ->where('user_id', $user->id)
            ->get();

        $submittedAttempts = Attempt::query()
            ->where('user_id', $user->id)
            ->where('status', AttemptStatusEnum::SUBMITTED)
            ->whereIn('mode', [AttemptModeEnum::PRACTICE, AttemptModeEnum::SIMULATION])
            ->get();

        $completedModules = $moduleProgresses->where('status', 'completed')->count();
        $practiceAttempts = $submittedAttempts->where('mode', AttemptModeEnum::PRACTICE)->count();
        $simulationAttempts = $submittedAttempts->where('mode', AttemptModeEnum::SIMULATION)->count();
        $strongAreas = $subtestAnalytics->filter(fn (array $item): bool => ($item['average_accuracy'] ?? 0) >= 80);
        $weakAreas = $subtestAnalytics->filter(fn (array $item): bool => ($item['average_accuracy'] ?? 100) < 60 && $item['attempts_count'] > 0);
        $decliningAreas = $subtestAnalytics->filter(fn (array $item): bool => ($item['trend']['direction'] ?? null) === 'declining');
        $averageAccuracy = (float) ($submittedAttempts->avg('accuracy') ?? 0);
        $completedTasks = $tasks->where('status', StudyPlanTaskStatusEnum::COMPLETED)->count();
        $totalTrackedTasks = max($tasks->where('is_active', true)->count() + $completedTasks, 1);

        return [
            'summary' => [
                'current_state' => $readiness['state'],
                'current_label' => $readiness['label'],
                'readiness_score' => (int) ($readiness['score'] ?? 0),
                'target_label' => $this->targetLabel($readiness['state'] ?? null),
                'execution_completion_rate' => (int) round(($completedTasks / $totalTrackedTasks) * 100),
                'completed_tasks' => $completedTasks,
                'open_tasks' => $tasks->filter(fn (StudyPlanTask $task): bool => $task->is_active && $task->status?->isOpen())->count(),
            ],
            'milestones' => [
                $this->milestone(
                    'foundation',
                    'Readiness review dasar',
                    $this->weightedProgress([
                        [$this->ratio($completedModules, 1), 40],
                        [$subtestAnalytics->isEmpty() ? 0 : 1 - min($weakAreas->count() / max($subtestAnalytics->count(), 1), 1), 40],
                        [$this->ratio($completedTasks, 2), 20],
                    ]),
                    [
                        ['label' => 'Modul selesai', 'current' => $completedModules, 'target' => 1],
                        ['label' => 'Weak area teratasi', 'current' => max($subtestAnalytics->count() - $weakAreas->count(), 0), 'target' => max($subtestAnalytics->count(), 1)],
                        ['label' => 'Task tereksekusi', 'current' => $completedTasks, 'target' => 2],
                    ],
                    'Fase ini menutup fondasi yang masih longgar sebelum practice makin berat.',
                ),
                $this->milestone(
                    'intermediate_practice',
                    'Readiness latihan menengah',
                    $this->weightedProgress([
                        [$this->ratio($completedModules, 2), 30],
                        [$this->ratio($practiceAttempts, 2), 40],
                        [$this->ratio($averageAccuracy, 70), 30],
                    ]),
                    [
                        ['label' => 'Modul selesai', 'current' => $completedModules, 'target' => 2],
                        ['label' => 'Practice submitted', 'current' => $practiceAttempts, 'target' => 2],
                        ['label' => 'Akurasi rata-rata', 'current' => (int) round($averageAccuracy), 'target' => 70],
                    ],
                    'Milestone ini memastikan ritme latihan sudah terbentuk, bukan hanya belajar materi.',
                ),
                $this->milestone(
                    'partial_simulation',
                    'Readiness simulasi parsial',
                    $this->weightedProgress([
                        [$this->ratio($strongAreas->count(), 1), 40],
                        [$this->ratio($practiceAttempts, 2), 35],
                        [$decliningAreas->isEmpty() ? 1 : 0, 25],
                    ]),
                    [
                        ['label' => 'Strong area', 'current' => $strongAreas->count(), 'target' => 1],
                        ['label' => 'Practice submitted', 'current' => $practiceAttempts, 'target' => 2],
                        ['label' => 'Area menurun', 'current' => max(1 - $decliningAreas->count(), 0), 'target' => 1],
                    ],
                    'Saat milestone ini kuat, user sudah siap menghadapi tekanan yang lebih formal pada subtes tertentu.',
                ),
                $this->milestone(
                    'full_simulation',
                    'Readiness simulasi penuh',
                    $this->weightedProgress([
                        [$this->ratio($strongAreas->count(), 2), 30],
                        [$this->ratio($completedModules, 2), 25],
                        [$this->ratio($practiceAttempts, 3), 25],
                        [$decliningAreas->isEmpty() ? 1 : 0, 10],
                        [$this->ratio($simulationAttempts, 1), 10],
                    ]),
                    [
                        ['label' => 'Strong area', 'current' => $strongAreas->count(), 'target' => 2],
                        ['label' => 'Modul selesai', 'current' => $completedModules, 'target' => 2],
                        ['label' => 'Practice submitted', 'current' => $practiceAttempts, 'target' => 3],
                        ['label' => 'Simulation submitted', 'current' => $simulationAttempts, 'target' => 1],
                    ],
                    'Target akhir ini menandai kesiapan untuk masuk ke ritme simulasi penuh secara konsisten.',
                ),
            ],
            'subtests' => $subtestAnalytics
                ->sortBy([
                    ['average_accuracy', 'asc'],
                    ['attempts_count', 'desc'],
                ])
                ->take(6)
                ->map(fn (array $item): array => $this->subtestRow($item))
                ->values()
                ->all(),
        ];
    }

    protected function milestone(string $id, string $label, int $progress, array $signals, string $description): array
    {
        return [
            'id' => $id,
            'label' => $label,
            'progress' => $progress,
            'state' => $progress >= 100 ? 'completed' : ($progress >= 60 ? 'in_progress' : 'starting'),
            'description' => $description,
            'signals' => $signals,
        ];
    }

    protected function subtestRow(array $item): array
    {
        $accuracy = (float) ($item['average_accuracy'] ?? 0);
        $practiceAttempts = (int) ($item['practice_attempts'] ?? 0);
        $trend = $item['trend']['direction'] ?? 'stable';

        if (($item['attempts_count'] ?? 0) === 0) {
            return [
                'subtest_name' => $item['subtest_name'],
                'subtest_slug' => $item['subtest_slug'],
                'label' => 'Perlu mulai dari modul dasar',
                'progress' => 15,
                'state' => 'foundation',
                'reason' => 'Belum ada attempt yang cukup untuk membaca kesiapan subtes ini.',
            ];
        }

        if ($accuracy < 60 || ($item['blank_rate'] ?? 0) >= 25) {
            return [
                'subtest_name' => $item['subtest_name'],
                'subtest_slug' => $item['subtest_slug'],
                'label' => 'Perlu review dasar',
                'progress' => max((int) round($accuracy), 20),
                'state' => 'foundation',
                'reason' => 'Akurasi masih rendah atau blank rate terlalu tinggi.',
            ];
        }

        if ($accuracy >= 80 && $practiceAttempts >= 2 && $trend !== 'declining') {
            return [
                'subtest_name' => $item['subtest_name'],
                'subtest_slug' => $item['subtest_slug'],
                'label' => 'Siap simulasi subtes',
                'progress' => min((int) round($accuracy), 95),
                'state' => 'simulation_ready',
                'reason' => 'Akurasi stabil tinggi dan practice sudah cukup.',
            ];
        }

        return [
            'subtest_name' => $item['subtest_name'],
            'subtest_slug' => $item['subtest_slug'],
            'label' => 'Siap latihan menengah',
            'progress' => max((int) round($accuracy), 45),
            'state' => 'practice_ready',
            'reason' => 'Fondasi mulai terbentuk tetapi masih butuh practice terarah.',
        ];
    }

    protected function ratio(float|int $current, float|int $target): float
    {
        if ($target <= 0) {
            return 0;
        }

        return min($current / $target, 1);
    }

    protected function weightedProgress(array $weights): int
    {
        $score = collect($weights)
            ->sum(fn (array $item): float => $item[0] * $item[1]);

        return (int) round(min($score, 100));
    }

    protected function targetLabel(?string $state): string
    {
        return match ($state) {
            'needs_foundation_review' => 'Bangun fondasi sampai siap latihan menengah',
            'ready_for_intermediate_practice' => 'Naikkan ritme practice terarah',
            'ready_for_subtest_simulation' => 'Dorong simulasi parsial pada area kuat',
            'ready_for_full_simulation' => 'Pertahankan readiness simulasi penuh',
            default => 'Jaga konsistensi belajar berikutnya',
        };
    }
}
