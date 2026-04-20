<?php

namespace App\Services;

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use App\Models\Attempt;
use App\Models\LearningModuleProgress;
use App\Models\StudyGoal;
use App\Models\StudyPlanTask;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GoalProgressAggregationService
{
    public function __construct(
        protected PlanGoalAlignmentEvaluationService $planGoalAlignmentEvaluationService,
    ) {}

    public function evaluate(
        StudyGoal $goal,
        User $user,
        Collection $tasks,
        array $readiness,
        array $readinessMilestones,
    ): array {
        $periodStart = $goal->period_starts_on->copy()->startOfDay();
        $periodEnd = $goal->period_ends_on->copy()->endOfDay();
        $targets = $goal->target_payload ?? [];
        $focusSubtestId = data_get($goal->focus_payload, 'subtest_id');

        $modulesCompleted = LearningModuleProgress::query()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$periodStart, $periodEnd])
            ->when(
                $focusSubtestId !== null,
                fn (Builder $query) => $query->whereHas(
                    'learningModule',
                    fn (Builder $learningModuleQuery) => $learningModuleQuery->where('subtest_id', $focusSubtestId),
                ),
            )
            ->count();

        $practiceCompleted = Attempt::query()
            ->where('user_id', $user->id)
            ->where('status', AttemptStatusEnum::SUBMITTED)
            ->where('mode', AttemptModeEnum::PRACTICE)
            ->whereBetween('submitted_at', [$periodStart, $periodEnd])
            ->when(
                $focusSubtestId !== null,
                fn (Builder $query) => $query->where('subtest_id', $focusSubtestId),
            )
            ->count();

        $simulationCompleted = Attempt::query()
            ->where('user_id', $user->id)
            ->where('status', AttemptStatusEnum::SUBMITTED)
            ->where('mode', AttemptModeEnum::SIMULATION)
            ->whereBetween('submitted_at', [$periodStart, $periodEnd])
            ->count();

        $completedAlignedTasks = $tasks
            ->filter(fn (StudyPlanTask $task): bool => $task->status?->value === 'completed')
            ->filter(fn (StudyPlanTask $task): bool => $task->completed_at !== null)
            ->filter(fn (StudyPlanTask $task): bool => $task->completed_at->between($periodStart, $periodEnd))
            ->filter(fn (StudyPlanTask $task): bool => $this->planGoalAlignmentEvaluationService->matches($goal, $task))
            ->count();

        $readinessDelta = (int) ($readiness['score'] ?? 0) - (int) data_get($goal->baseline_payload, 'readiness_score', 0);
        $baselineAccuracy = data_get($goal->baseline_payload, 'focus_average_accuracy');
        $currentAccuracy = $this->currentAccuracy($user, $focusSubtestId, $periodStart, $periodEnd);
        $accuracyDelta = $baselineAccuracy !== null && $currentAccuracy !== null
            ? round($currentAccuracy - (float) $baselineAccuracy, 2)
            : null;

        $alignment = $this->planGoalAlignmentEvaluationService->evaluate($goal, $tasks);
        $milestone = collect($readinessMilestones['milestones'] ?? [])
            ->firstWhere('id', data_get($targets, 'milestone_id'));

        $progressSignals = collect([
            $this->signalRatio($modulesCompleted, (int) data_get($targets, 'modules_completed', 0)),
            $this->signalRatio($practiceCompleted, (int) data_get($targets, 'practice_completed', 0)),
            $this->signalRatio($simulationCompleted, (int) data_get($targets, 'simulation_completed', 0)),
            $this->signalRatio($completedAlignedTasks, (int) data_get($targets, 'task_completions', 0)),
            $this->signalRatio(max($readinessDelta, 0), (int) data_get($targets, 'readiness_score_delta', 0)),
        ])->filter();

        $progress = $progressSignals->isEmpty()
            ? 0
            : (int) round($progressSignals->avg() * 100);

        $elapsedProgress = $this->elapsedProgress($periodStart, $periodEnd);
        $status = $this->status($progress, $elapsedProgress);

        return [
            'id' => $goal->id,
            'key' => $goal->goal_key,
            'period_type' => $goal->period_type?->value,
            'period_label' => $goal->period_type?->label(),
            'goal_type' => $goal->goal_type,
            'title' => $goal->title,
            'description' => $goal->description,
            'rationale' => $goal->rationale,
            'window_label' => $this->windowLabel($goal->period_starts_on, $goal->period_ends_on),
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'progress' => $progress,
            'elapsed_progress' => $elapsedProgress,
            'pace_label' => $this->paceLabel($status, $progress, $elapsedProgress),
            'focus' => [
                'subtest_name' => data_get($goal->focus_payload, 'subtest_name'),
                'subtest_slug' => data_get($goal->focus_payload, 'subtest_slug'),
                'tracks' => data_get($goal->focus_payload, 'tracks', []),
                'target_readiness_label' => data_get($goal->focus_payload, 'target_readiness_label'),
            ],
            'targets' => collect([
                $this->targetRow('Modul selesai', $modulesCompleted, (int) data_get($targets, 'modules_completed', 0)),
                $this->targetRow('Practice selesai', $practiceCompleted, (int) data_get($targets, 'practice_completed', 0)),
                $this->targetRow('Simulation selesai', $simulationCompleted, (int) data_get($targets, 'simulation_completed', 0)),
                $this->targetRow('Task relevan selesai', $completedAlignedTasks, (int) data_get($targets, 'task_completions', 0)),
                $this->targetRow('Kenaikan readiness score', max($readinessDelta, 0), (int) data_get($targets, 'readiness_score_delta', 0)),
            ])->filter()->values()->all(),
            'outcomes' => [
                'readiness_score_delta' => $readinessDelta,
                'accuracy_delta' => $accuracyDelta,
                'baseline_readiness_label' => data_get($goal->baseline_payload, 'readiness_label'),
                'current_readiness_label' => $readiness['label'] ?? null,
                'readiness_changed' => data_get($goal->baseline_payload, 'readiness_state') !== ($readiness['state'] ?? null),
            ],
            'alignment' => $alignment,
            'milestone' => $milestone
                ? [
                    'id' => $milestone['id'],
                    'label' => $milestone['label'],
                    'progress' => $milestone['progress'],
                    'state' => $milestone['state'],
                ]
                : null,
        ];
    }

    protected function currentAccuracy(User $user, ?int $focusSubtestId, CarbonInterface $periodStart, CarbonInterface $periodEnd): ?float
    {
        if ($focusSubtestId === null) {
            return null;
        }

        $value = Attempt::query()
            ->where('user_id', $user->id)
            ->where('status', AttemptStatusEnum::SUBMITTED)
            ->whereIn('mode', [AttemptModeEnum::PRACTICE, AttemptModeEnum::SIMULATION])
            ->whereBetween('submitted_at', [$periodStart, $periodEnd])
            ->where('subtest_id', $focusSubtestId)
            ->avg('accuracy');

        return $value !== null ? round((float) $value, 2) : null;
    }

    protected function signalRatio(int $current, int $target): ?float
    {
        if ($target <= 0) {
            return null;
        }

        return min($current / $target, 1);
    }

    protected function elapsedProgress(CarbonInterface $periodStart, CarbonInterface $periodEnd): int
    {
        $today = today();
        $effectiveEnd = $today->lte($periodEnd) ? $today : $periodEnd;
        $totalDays = max($periodStart->diffInDays($periodEnd) + 1, 1);
        $elapsedDays = min(max($periodStart->diffInDays($effectiveEnd) + 1, 0), $totalDays);

        return (int) round(($elapsedDays / $totalDays) * 100);
    }

    protected function status(int $progress, int $elapsedProgress): string
    {
        if ($progress >= 100) {
            return 'completed';
        }

        return $progress + 10 >= $elapsedProgress ? 'on_track' : 'off_track';
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'completed' => 'Target tercapai',
            'on_track' => 'Sesuai jalur',
            default => 'Di luar jalur',
        };
    }

    protected function paceLabel(string $status, int $progress, int $elapsedProgress): string
    {
        return match ($status) {
            'completed' => 'Target periode ini sudah tercapai.',
            'on_track' => 'Progres '.$progress.'% masih sejalan dengan pace periode '.$elapsedProgress.'%.',
            default => 'Progres '.$progress.'% tertinggal dari pace periode '.$elapsedProgress.'%.',
        };
    }

    protected function windowLabel(CarbonInterface $periodStart, CarbonInterface $periodEnd): string
    {
        return $periodStart->translatedFormat('d M').' - '.$periodEnd->translatedFormat('d M');
    }

    protected function targetRow(string $label, int $current, int $target): ?array
    {
        if ($target <= 0) {
            return null;
        }

        return [
            'label' => $label,
            'current' => $current,
            'target' => $target,
        ];
    }
}

