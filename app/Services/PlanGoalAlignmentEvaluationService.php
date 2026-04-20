<?php

namespace App\Services;

use App\Enums\StudyPlanTaskStatusEnum;
use App\Models\StudyGoal;
use App\Models\StudyPlanTask;
use Illuminate\Support\Collection;

class PlanGoalAlignmentEvaluationService
{
    public function __construct(
        protected StudyPlanAgendaService $studyPlanAgendaService,
    ) {}

    public function evaluate(StudyGoal $goal, Collection $tasks): array
    {
        $alignedTasks = $tasks
            ->filter(fn (StudyPlanTask $task): bool => $this->matches($goal, $task))
            ->values();

        $openTasks = $alignedTasks
            ->filter(fn (StudyPlanTask $task): bool => $task->is_active && $task->status?->isOpen())
            ->values();

        $completedTasks = $alignedTasks
            ->filter(fn (StudyPlanTask $task): bool => $task->status === StudyPlanTaskStatusEnum::COMPLETED)
            ->values();

        $dueToday = $openTasks
            ->filter(fn (StudyPlanTask $task): bool => $this->studyPlanAgendaService->serialize($task)['is_due_today'])
            ->count();

        $score = min(
            ($openTasks->count() * 20)
            + ($completedTasks->count() * 15)
            + ($dueToday > 0 ? 15 : 0),
            100,
        );

        return [
            'score' => $score,
            'label' => $this->alignmentLabel($score),
            'description' => $this->description($goal, $openTasks->count(), $completedTasks->count(), $dueToday),
            'supporting_open_tasks' => $openTasks->count(),
            'supporting_completed_tasks' => $completedTasks->count(),
            'supporting_due_today' => $dueToday,
            'next_aligned_action' => $openTasks->first()
                ? $this->studyPlanAgendaService->serialize($openTasks->first())
                : null,
        ];
    }

    public function matches(StudyGoal $goal, StudyPlanTask $task): bool
    {
        $focusSubtestId = data_get($goal->focus_payload, 'subtest_id');
        $goalTracks = collect(data_get($goal->focus_payload, 'tracks', []));
        $taskSubtestId = data_get($task->metadata, 'target.subtest_id');

        if ($focusSubtestId !== null && (int) $taskSubtestId === (int) $focusSubtestId) {
            return true;
        }

        return $goalTracks->contains($task->track);
    }

    protected function alignmentLabel(int $score): string
    {
        return match (true) {
            $score >= 80 => 'Plan sangat selaras',
            $score >= 55 => 'Plan cukup selaras',
            default => 'Plan perlu dipertegas',
        };
    }

    protected function description(StudyGoal $goal, int $openTasks, int $completedTasks, int $dueToday): string
    {
        $focusName = data_get($goal->focus_payload, 'subtest_name', 'target utama');

        if ($openTasks === 0 && $completedTasks === 0) {
            return 'Belum ada task aktif yang benar-benar mendorong goal '.$focusName.'.';
        }

        return trim(collect([
            $completedTasks > 0 ? $completedTasks.' task relevan sudah selesai.' : null,
            $openTasks > 0 ? $openTasks.' task aktif masih menopang goal ini.' : null,
            $dueToday > 0 ? $dueToday.' di antaranya jatuh tempo hari ini.' : null,
        ])->filter()->implode(' '));
    }
}
