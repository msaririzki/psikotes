<?php

namespace App\Services;

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use App\Enums\ModuleLevelEnum;
use App\Models\Attempt;
use App\Models\LearningModule;
use App\Models\LearningModuleProgress;
use App\Models\User;
use Illuminate\Support\Collection;

class StudyPlanGenerationService
{
    public function __construct(
        protected SubtestAnalyticsService $subtestAnalyticsService,
        protected ReadinessEvaluationService $readinessEvaluationService,
        protected ReviewSchedulingService $reviewSchedulingService,
        protected RecommendationPrioritizationService $recommendationPrioritizationService,
    ) {}

    public function forUser(User $user): array
    {
        $subtestAnalyticsPayload = $this->subtestAnalyticsService->forUser($user);
        $subtestAnalytics = collect($subtestAnalyticsPayload['items']);

        $moduleProgresses = LearningModuleProgress::query()
            ->where('user_id', $user->id)
            ->with('learningModule.subtest.category')
            ->get();

        $submittedAttempts = Attempt::query()
            ->where('user_id', $user->id)
            ->where('status', AttemptStatusEnum::SUBMITTED)
            ->whereIn('mode', [
                AttemptModeEnum::MINI_QUIZ,
                AttemptModeEnum::PRACTICE,
                AttemptModeEnum::SIMULATION,
            ])
            ->with(['subtest.category', 'learningModule', 'simulationPackage'])
            ->latest('submitted_at')
            ->get();

        $readiness = $this->readinessEvaluationService->evaluate(
            $subtestAnalytics,
            $moduleProgresses,
            $submittedAttempts,
        );

        $reviewQueue = collect($this->reviewSchedulingService->build(
            $moduleProgresses,
            $subtestAnalytics,
            $submittedAttempts,
        ));

        $candidateTasks = $this->candidateTasks(
            $subtestAnalytics,
            $moduleProgresses,
            $submittedAttempts,
            $reviewQueue,
            $readiness,
        );

        $prioritized = collect(
            $this->recommendationPrioritizationService->prioritize($candidateTasks, $readiness),
        );

        return [
            'readiness' => $readiness,
            'next_best_action' => $prioritized->first(),
            'priority_recommendations' => $prioritized->take(5)->values()->all(),
            'review_queue' => $reviewQueue->values()->all(),
            'plan_sections' => [
                'immediate' => $prioritized->take(3)->values()->all(),
                'upcoming' => $prioritized->slice(3, 3)->values()->all(),
            ],
            'transparency' => [
                'Weakest area selalu diprioritaskan lebih dulu daripada challenge baru.',
                'Modul dasar didahulukan sebelum latihan lanjut saat fondasi belum stabil.',
                'Practice diutamakan sebelum simulation jika readiness penuh belum tercapai.',
                'Review queue dibangun dari modul lama, subtes menurun, dan hasil simulation yang masih lemah.',
            ],
        ];
    }

    protected function candidateTasks(
        Collection $subtestAnalytics,
        Collection $moduleProgresses,
        Collection $submittedAttempts,
        Collection $reviewQueue,
        array $readiness,
    ): Collection {
        $tasks = collect();

        $weakArea = $subtestAnalytics
            ->filter(fn (array $item): bool => ($item['average_accuracy'] ?? 100) < 60 && $item['attempts_count'] > 0)
            ->sortBy('average_accuracy')
            ->first();

        if ($weakArea) {
            $basicModule = LearningModule::query()
                ->where('subtest_id', $weakArea['subtest_id'])
                ->where('is_published', true)
                ->where('level', ModuleLevelEnum::BASIC)
                ->orderBy('published_at')
                ->orderBy('title')
                ->first();

            $tasks->push([
                'id' => 'weak-foundation-'.$weakArea['subtest_id'],
                'type' => 'foundation_module',
                'track' => 'learn',
                'title' => 'Pelajari ulang fondasi '.$weakArea['subtest_name'],
                'description' => 'Sebelum practice berikutnya, rapikan pola dasar subtes ini lewat modul inti yang paling relevan.',
                'reason' => 'Akurasi '.$weakArea['average_accuracy'].'% dengan blank rate '.$weakArea['blank_rate'].'%.',
                'action_label' => $basicModule ? 'Buka modul' : 'Buka learn',
                'action_href' => $basicModule ? '/learn/modules/'.$basicModule->slug : '/learn',
                'priority_score' => 92,
                'due_label' => 'Hari ini',
                'recommended_for_date' => now()->toDateString(),
                'target' => [
                    'subtest_id' => $weakArea['subtest_id'],
                    'learning_module_id' => $basicModule?->id,
                ],
                'auto_resolve_on' => [
                    StudyPlanTaskActivityMatcherService::ACTIVITY_LEARNING_MODULE_COMPLETED,
                ],
                'source' => 'weakest_area',
            ]);
        }

        $stagnantArea = $subtestAnalytics->first(function (array $item): bool {
            return $item['attempts_count'] >= 3
                && ($item['average_accuracy'] ?? 100) < 75
                && ($item['trend']['direction'] ?? null) === 'stable';
        });

        if ($stagnantArea) {
            $tasks->push([
                'id' => 'stagnant-practice-'.$stagnantArea['subtest_id'],
                'type' => 'stagnation_practice',
                'track' => 'practice',
                'title' => 'Ulang practice '.$stagnantArea['subtest_name'].' dengan beban lebih ringan',
                'description' => 'Performa belum naik walau attempt sudah berulang. Turunkan target dulu agar akurasi kembali rapi.',
                'reason' => 'Tiga attempt terakhir stagnan di bawah 75%.',
                'action_label' => 'Buka practice',
                'action_href' => '/practice/subtests/'.$stagnantArea['subtest_slug'],
                'priority_score' => 84,
                'due_label' => 'Dalam 2 hari',
                'recommended_for_date' => now()->addDays(2)->toDateString(),
                'target' => [
                    'subtest_id' => $stagnantArea['subtest_id'],
                ],
                'auto_resolve_on' => [
                    StudyPlanTaskActivityMatcherService::ACTIVITY_PRACTICE_SUBMITTED,
                ],
                'source' => 'stagnation',
            ]);
        }

        $strongArea = $subtestAnalytics->first(function (array $item): bool {
            return ($item['average_accuracy'] ?? 0) >= 80
                && ($item['trend']['direction'] ?? null) !== 'declining'
                && $item['practice_attempts'] >= 1;
        });

        if ($strongArea && in_array($readiness['state'], ['ready_for_subtest_simulation', 'ready_for_full_simulation'], true)) {
            $tasks->push([
                'id' => 'simulation-readiness-'.$strongArea['subtest_id'],
                'type' => 'simulation_push',
                'track' => 'simulation',
                'title' => 'Masuk simulation setelah '.$strongArea['subtest_name'],
                'description' => 'Area ini sudah kuat dan stabil. Gunakan momentumnya untuk menguji ritme kerja di paket simulation.',
                'reason' => 'Akurasi rata-rata '.$strongArea['average_accuracy'].'% dengan tren '.$strongArea['trend']['direction'].'.',
                'action_label' => 'Buka simulations',
                'action_href' => '/simulations',
                'priority_score' => 76,
                'due_label' => 'Besok',
                'recommended_for_date' => now()->addDay()->toDateString(),
                'target' => [
                    'subtest_id' => $strongArea['subtest_id'],
                ],
                'auto_resolve_on' => [
                    StudyPlanTaskActivityMatcherService::ACTIVITY_SIMULATION_SUBMITTED,
                ],
                'source' => 'simulation_readiness',
            ]);
        }

        $recentSimulation = $submittedAttempts
            ->where('mode', AttemptModeEnum::SIMULATION)
            ->sortByDesc('submitted_at')
            ->first();

        if ($recentSimulation && (float) ($recentSimulation->score_total ?? 0) < 75) {
            $tasks->push([
                'id' => 'recent-simulation-review-'.$recentSimulation->id,
                'type' => 'simulation_review',
                'track' => 'review',
                'title' => 'Review hasil simulation terbaru',
                'description' => 'Simulation terakhir masih menyimpan titik lemah yang bisa langsung dipakai sebagai bahan perbaikan.',
                'reason' => 'Skor terakhir '.(float) ($recentSimulation->score_total ?? 0).'%.',
                'action_label' => 'Buka hasil simulation',
                'action_href' => '/simulations/attempts/'.$recentSimulation->id.'/result',
                'priority_score' => 79,
                'due_label' => 'Hari ini',
                'recommended_for_date' => now()->toDateString(),
                'target' => [
                    'attempt_id' => $recentSimulation->id,
                ],
                'auto_resolve_on' => [
                    StudyPlanTaskActivityMatcherService::ACTIVITY_SIMULATION_RESULT_VIEWED,
                ],
                'source' => 'recent_simulation',
            ]);
        }

        $neverCompletedModule = $moduleProgresses
            ->filter(fn (LearningModuleProgress $progress): bool => $progress->status?->value !== 'completed')
            ->sortBy('last_viewed_at')
            ->first();

        if ($neverCompletedModule?->learningModule?->is_published) {
            $tasks->push([
                'id' => 'complete-module-'.$neverCompletedModule->learning_module_id,
                'type' => 'module_completion',
                'track' => 'learn',
                'title' => 'Selesaikan modul '.$neverCompletedModule->learningModule->title,
                'description' => 'Modul ini sudah dibuka tetapi belum selesai. Menuntaskan fondasi akan membuat practice berikutnya lebih terarah.',
                'reason' => 'Modul masih berstatus sedang dipelajari.',
                'action_label' => 'Lanjutkan modul',
                'action_href' => '/learn/modules/'.$neverCompletedModule->learningModule->slug,
                'priority_score' => 73,
                'due_label' => 'Dalam 3 hari',
                'recommended_for_date' => now()->addDays(3)->toDateString(),
                'target' => [
                    'subtest_id' => $neverCompletedModule->learningModule->subtest_id,
                    'learning_module_id' => $neverCompletedModule->learning_module_id,
                ],
                'auto_resolve_on' => [
                    StudyPlanTaskActivityMatcherService::ACTIVITY_LEARNING_MODULE_COMPLETED,
                ],
                'source' => 'unfinished_module',
            ]);
        }

        return $tasks
            ->merge($reviewQueue)
            ->unique('id')
            ->values();
    }
}
