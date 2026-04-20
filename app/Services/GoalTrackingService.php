<?php

namespace App\Services;

use App\Enums\StudyGoalPeriodEnum;
use App\Models\StudyGoal;
use App\Models\User;
use Illuminate\Support\Collection;

class GoalTrackingService
{
    public function __construct(
        protected SubtestAnalyticsService $subtestAnalyticsService,
        protected GoalProgressAggregationService $goalProgressAggregationService,
    ) {}

    public function forUser(User $user, array $generatedPlan, Collection $tasks, array $readinessMilestones): array
    {
        $goals = $this->syncForUser($user, $generatedPlan);

        $evaluatedGoals = $goals
            ->map(fn (StudyGoal $goal): array => $this->goalProgressAggregationService->evaluate(
                $goal,
                $user,
                $tasks,
                $generatedPlan['readiness'],
                $readinessMilestones,
            ))
            ->sortBy(fn (array $goal): int => $goal['period_type'] === StudyGoalPeriodEnum::WEEKLY->value ? 0 : 1)
            ->values();

        return [
            'summary' => [
                'on_track_goals' => $evaluatedGoals->where('status', 'on_track')->count(),
                'off_track_goals' => $evaluatedGoals->where('status', 'off_track')->count(),
                'completed_goals' => $evaluatedGoals->where('status', 'completed')->count(),
                'primary_focus' => data_get($generatedPlan, 'next_best_action.title'),
                'readiness_target' => data_get($readinessMilestones, 'summary.target_label'),
            ],
            'primary_goal' => $evaluatedGoals->first(),
            'active_goals' => $evaluatedGoals->all(),
        ];
    }

    protected function syncForUser(User $user, array $generatedPlan): Collection
    {
        $analytics = collect($this->subtestAnalyticsService->forUser($user)['items']);
        $blueprints = collect([
            $this->weeklyBlueprint($generatedPlan, $analytics),
            $this->monthlyBlueprint($generatedPlan, $analytics),
        ]);

        $goalKeys = $blueprints->pluck('goal_key')->all();

        StudyGoal::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->when(
                $goalKeys !== [],
                fn ($query) => $query->whereNotIn('goal_key', $goalKeys),
                fn ($query) => $query,
            )
            ->update(['is_active' => false]);

        foreach ($blueprints as $blueprint) {
            $goal = StudyGoal::query()->firstOrNew([
                'user_id' => $user->id,
                'goal_key' => $blueprint['goal_key'],
            ]);

            if (! $goal->exists) {
                $goal->fill($blueprint);
            }

            $goal->forceFill([
                'is_active' => true,
                'last_generated_at' => now(),
                'metadata' => array_filter([
                    ...($goal->metadata ?? []),
                    'latest_readiness_state' => data_get($generatedPlan, 'readiness.state'),
                    'latest_readiness_label' => data_get($generatedPlan, 'readiness.label'),
                    'next_best_action_id' => data_get($generatedPlan, 'next_best_action.id'),
                    'next_best_action_title' => data_get($generatedPlan, 'next_best_action.title'),
                ]),
            ])->save();
        }

        return StudyGoal::query()
            ->where('user_id', $user->id)
            ->whereIn('goal_key', $goalKeys)
            ->orderByRaw("case when period_type = 'weekly' then 0 else 1 end")
            ->get();
    }

    protected function weeklyBlueprint(array $generatedPlan, Collection $analytics): array
    {
        [$goalType, $tracks, $targets, $title, $description, $rationale] = $this->periodProfile(
            'weekly',
            $generatedPlan,
            $analytics,
        );

        $focus = $this->focusPayload($generatedPlan, $analytics, $tracks);
        $periodStart = today()->copy()->startOfWeek();
        $periodEnd = today()->copy()->endOfWeek();

        return [
            'goal_key' => 'weekly-'.$periodStart->toDateString(),
            'period_type' => StudyGoalPeriodEnum::WEEKLY,
            'goal_type' => $goalType,
            'title' => $title,
            'description' => $description,
            'rationale' => $rationale,
            'period_starts_on' => $periodStart->toDateString(),
            'period_ends_on' => $periodEnd->toDateString(),
            'focus_payload' => $focus,
            'target_payload' => $targets,
            'baseline_payload' => $this->baselinePayload($generatedPlan, $focus, $analytics),
            'metadata' => [
                'goal_window' => 'weekly',
                'source_task_id' => data_get($generatedPlan, 'next_best_action.id'),
            ],
        ];
    }

    protected function monthlyBlueprint(array $generatedPlan, Collection $analytics): array
    {
        [$goalType, $tracks, $targets, $title, $description, $rationale] = $this->periodProfile(
            'monthly',
            $generatedPlan,
            $analytics,
        );

        $focus = $this->focusPayload($generatedPlan, $analytics, $tracks);
        $periodStart = today()->copy()->startOfMonth();
        $periodEnd = today()->copy()->endOfMonth();

        return [
            'goal_key' => 'monthly-'.$periodStart->toDateString(),
            'period_type' => StudyGoalPeriodEnum::MONTHLY,
            'goal_type' => $goalType,
            'title' => $title,
            'description' => $description,
            'rationale' => $rationale,
            'period_starts_on' => $periodStart->toDateString(),
            'period_ends_on' => $periodEnd->toDateString(),
            'focus_payload' => $focus,
            'target_payload' => $targets,
            'baseline_payload' => $this->baselinePayload($generatedPlan, $focus, $analytics),
            'metadata' => [
                'goal_window' => 'monthly',
                'source_task_id' => data_get($generatedPlan, 'next_best_action.id'),
            ],
        ];
    }

    protected function periodProfile(string $period, array $generatedPlan, Collection $analytics): array
    {
        $readinessState = data_get($generatedPlan, 'readiness.state');
        $focusName = $this->focusName($generatedPlan, $analytics);

        if ($period === 'weekly') {
            return match ($readinessState) {
                'needs_foundation_review' => [
                    'foundation_reset',
                    ['learn', 'review'],
                    [
                        'modules_completed' => 1,
                        'practice_completed' => 1,
                        'simulation_completed' => 0,
                        'task_completions' => 3,
                        'readiness_score_delta' => 8,
                        'milestone_id' => 'foundation',
                    ],
                    'Stabilkan fondasi '.$focusName.' minggu ini',
                    'Fokus mingguan ini adalah menutup celah dasar supaya practice berikutnya tidak dibangun di atas pola yang masih rapuh.',
                    'Goal ini muncul karena weakest area masih dominan dan readiness belum cukup kuat untuk ritme latihan menengah.',
                ],
                'ready_for_intermediate_practice' => [
                    'practice_momentum',
                    ['learn', 'practice', 'review'],
                    [
                        'modules_completed' => 1,
                        'practice_completed' => 2,
                        'simulation_completed' => 0,
                        'task_completions' => 4,
                        'readiness_score_delta' => 8,
                        'milestone_id' => 'intermediate_practice',
                    ],
                    'Dorong momentum practice '.$focusName.' minggu ini',
                    'Goal mingguan ini menjaga agar belajar tidak berhenti di materi, tetapi benar-benar berubah menjadi ritme practice terarah.',
                    'Readiness sudah bergerak ke practice, jadi task dan cadence perlu diarahkan ke attempt yang lebih konsisten.',
                ],
                default => [
                    'simulation_push',
                    ['practice', 'simulation', 'review'],
                    [
                        'modules_completed' => 0,
                        'practice_completed' => 2,
                        'simulation_completed' => 1,
                        'task_completions' => 4,
                        'readiness_score_delta' => 6,
                        'milestone_id' => $readinessState === 'ready_for_full_simulation' ? 'full_simulation' : 'partial_simulation',
                    ],
                    'Konversi area kuat '.$focusName.' ke simulation minggu ini',
                    'Goal mingguan ini menguji apakah effort harian benar-benar berubah menjadi readiness simulation yang lebih nyata.',
                    'User sudah dekat dengan simulation, jadi periode ini perlu menghubungkan practice, review, dan attempt formal.',
                ],
            };
        }

        return match ($readinessState) {
            'needs_foundation_review' => [
                'foundation_recovery',
                ['learn', 'practice', 'review'],
                [
                    'modules_completed' => 2,
                    'practice_completed' => 3,
                    'simulation_completed' => 0,
                    'task_completions' => 8,
                    'readiness_score_delta' => 15,
                    'milestone_id' => 'intermediate_practice',
                ],
                'Capai readiness latihan menengah bulan ini',
                'Target bulanan ini mengubah pembenahan fondasi menjadi kesiapan practice yang lebih stabil dan terukur.',
                'Karena weakest area masih berat, target bulan ini memprioritaskan perbaikan fondasi sebelum mengejar simulation.',
            ],
            'ready_for_intermediate_practice' => [
                'practice_consolidation',
                ['learn', 'practice', 'review'],
                [
                    'modules_completed' => 2,
                    'practice_completed' => 4,
                    'simulation_completed' => 1,
                    'task_completions' => 10,
                    'readiness_score_delta' => 15,
                    'milestone_id' => 'partial_simulation',
                ],
                'Naikkan practice menjadi kesiapan simulasi parsial',
                'Target bulanan ini menilai apakah cadence dan task yang dijalankan benar-benar menghasilkan akurasi yang cukup stabil untuk simulation parsial.',
                'Readiness sudah keluar dari fase dasar, jadi bulan ini harus menunjukkan outcome nyata di practice dan review.',
            ],
            'ready_for_subtest_simulation' => [
                'subtest_simulation_push',
                ['practice', 'simulation', 'review'],
                [
                    'modules_completed' => 1,
                    'practice_completed' => 4,
                    'simulation_completed' => 1,
                    'task_completions' => 10,
                    'readiness_score_delta' => 12,
                    'milestone_id' => 'partial_simulation',
                ],
                'Bangun simulasi parsial yang stabil bulan ini',
                'Goal bulanan ini memastikan readiness simulation tidak hanya muncul sesekali, tetapi benar-benar ditopang oleh practice dan review yang konsisten.',
                'Area kuat sudah terlihat, sehingga target sekarang adalah membuat hasil simulation parsial menjadi repeatable.',
            ],
            default => [
                'full_simulation_maintenance',
                ['practice', 'simulation', 'review'],
                [
                    'modules_completed' => 1,
                    'practice_completed' => 4,
                    'simulation_completed' => 2,
                    'task_completions' => 10,
                    'readiness_score_delta' => 10,
                    'milestone_id' => 'full_simulation',
                ],
                'Pertahankan readiness simulasi penuh bulan ini',
                'Goal bulanan ini menguji apakah execution loop yang sudah berjalan benar-benar mempertahankan readiness menuju simulation penuh.',
                'Saat readiness sudah tinggi, fokusnya bergeser dari membangun fondasi menjadi menjaga kualitas performa tetap stabil.',
            ],
        };
    }

    protected function focusPayload(array $generatedPlan, Collection $analytics, array $tracks): array
    {
        $focusSubtestId = data_get($generatedPlan, 'next_best_action.target.subtest_id')
            ?? data_get($generatedPlan, 'priority_recommendations.0.target.subtest_id');

        $focus = $focusSubtestId !== null
            ? $analytics->firstWhere('subtest_id', $focusSubtestId)
            : $analytics->sortBy('average_accuracy')->first();

        return [
            'subtest_id' => $focus['subtest_id'] ?? null,
            'subtest_name' => $focus['subtest_name'] ?? null,
            'subtest_slug' => $focus['subtest_slug'] ?? null,
            'tracks' => $tracks,
            'target_readiness_label' => data_get($generatedPlan, 'readiness.label'),
        ];
    }

    protected function baselinePayload(array $generatedPlan, array $focus, Collection $analytics): array
    {
        $focusAnalytics = isset($focus['subtest_id'])
            ? $analytics->firstWhere('subtest_id', $focus['subtest_id'])
            : null;

        return [
            'readiness_state' => data_get($generatedPlan, 'readiness.state'),
            'readiness_label' => data_get($generatedPlan, 'readiness.label'),
            'readiness_score' => (int) data_get($generatedPlan, 'readiness.score', 0),
            'focus_average_accuracy' => $focusAnalytics['average_accuracy'] ?? null,
        ];
    }

    protected function focusName(array $generatedPlan, Collection $analytics): string
    {
        $focusSubtestId = data_get($generatedPlan, 'next_best_action.target.subtest_id');
        $focus = $focusSubtestId !== null
            ? $analytics->firstWhere('subtest_id', $focusSubtestId)
            : $analytics->sortBy('average_accuracy')->first();

        return $focus['subtest_name'] ?? 'area prioritas';
    }
}
