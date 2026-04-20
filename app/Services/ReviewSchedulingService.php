<?php

namespace App\Services;

use App\Enums\AttemptModeEnum;
use App\Models\Attempt;
use App\Models\LearningModuleProgress;
use Illuminate\Support\Collection;

class ReviewSchedulingService
{
    public function build(Collection $moduleProgresses, Collection $subtestAnalytics, Collection $submittedAttempts): array
    {
        $queue = collect();

        $staleModules = $moduleProgresses
            ->filter(fn (LearningModuleProgress $progress): bool => $progress->last_viewed_at !== null)
            ->filter(fn (LearningModuleProgress $progress): bool => $progress->last_viewed_at->lte(now()->subDays(7)))
            ->sortBy('last_viewed_at');

        foreach ($staleModules as $progress) {
            if (! $progress->learningModule?->is_published) {
                continue;
            }

            $days = max((int) $progress->last_viewed_at?->diffInDays(now()), 1);

            $queue->push([
                'id' => 'review-module-'.$progress->id,
                'type' => 'review_module',
                'track' => 'learn',
                'title' => 'Review modul '.$progress->learningModule->title,
                'description' => 'Materi ini sudah lama tidak dibuka. Review singkat akan menjaga pemahaman sebelum attempt berikutnya.',
                'reason' => 'Terakhir dibuka '.$days.' hari lalu.',
                'action_label' => 'Buka modul',
                'action_href' => '/learn/modules/'.$progress->learningModule->slug,
                'priority_score' => min(70 + $days, 95),
                'due_label' => 'Hari ini',
                'recommended_for_date' => now()->toDateString(),
                'target' => [
                    'subtest_id' => $progress->learningModule->subtest_id,
                    'learning_module_id' => $progress->learning_module_id,
                ],
                'auto_resolve_on' => [
                    StudyPlanTaskActivityMatcherService::ACTIVITY_LEARNING_MODULE_VIEWED,
                    StudyPlanTaskActivityMatcherService::ACTIVITY_LEARNING_MODULE_COMPLETED,
                ],
                'source' => 'stale_module',
            ]);
        }

        $decliningAreas = collect($subtestAnalytics)
            ->filter(fn (array $item): bool => ($item['trend']['direction'] ?? null) === 'declining');

        foreach ($decliningAreas as $item) {
            $queue->push([
                'id' => 'review-subtest-'.$item['subtest_id'],
                'type' => 'review_declining_subtest',
                'track' => 'practice',
                'title' => 'Review penurunan di '.$item['subtest_name'],
                'description' => 'Performa subtes ini sedang menurun. Review pembahasan attempt terbaru sebelum masuk ke latihan berikutnya.',
                'reason' => 'Tren terbaru menurun '.$item['trend']['delta'].' poin.',
                'action_label' => 'Buka practice',
                'action_href' => '/practice/subtests/'.$item['subtest_slug'],
                'priority_score' => 82,
                'due_label' => 'Dalam 2 hari',
                'recommended_for_date' => now()->addDays(2)->toDateString(),
                'target' => [
                    'subtest_id' => $item['subtest_id'],
                ],
                'auto_resolve_on' => [
                    StudyPlanTaskActivityMatcherService::ACTIVITY_PRACTICE_SUBMITTED,
                    StudyPlanTaskActivityMatcherService::ACTIVITY_PRACTICE_RESULT_VIEWED,
                ],
                'source' => 'declining_subtest',
            ]);
        }

        $recentLowSimulation = $submittedAttempts
            ->where('mode', AttemptModeEnum::SIMULATION)
            ->filter(fn (Attempt $attempt): bool => (float) ($attempt->score_total ?? 0) < 75)
            ->sortByDesc('submitted_at')
            ->first();

        if ($recentLowSimulation) {
            $queue->push([
                'id' => 'review-simulation-'.$recentLowSimulation->id,
                'type' => 'review_simulation_result',
                'track' => 'simulation',
                'title' => 'Review hasil simulation terakhir',
                'description' => 'Simulation yang baru selesai masih menyimpan weak point yang penting untuk dibaca ulang.',
                'reason' => 'Skor simulation terakhir '.(float) ($recentLowSimulation->score_total ?? 0).'.',
                'action_label' => 'Buka hasil',
                'action_href' => '/simulations/attempts/'.$recentLowSimulation->id.'/result',
                'priority_score' => 78,
                'due_label' => 'Besok',
                'recommended_for_date' => now()->addDay()->toDateString(),
                'target' => [
                    'attempt_id' => $recentLowSimulation->id,
                ],
                'auto_resolve_on' => [
                    StudyPlanTaskActivityMatcherService::ACTIVITY_SIMULATION_RESULT_VIEWED,
                ],
                'source' => 'simulation_review',
            ]);
        }

        return $queue
            ->sortByDesc('priority_score')
            ->unique('id')
            ->values()
            ->take(5)
            ->all();
    }
}
