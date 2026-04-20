<?php

namespace App\Services;

use App\Enums\StudyPlanTaskStatusEnum;
use App\Models\StudyPlanTask;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class StudyPlanAgendaService
{
    public function build(Collection $tasks): array
    {
        $today = today();
        $openTasks = $tasks
            ->filter(fn (StudyPlanTask $task): bool => $task->is_active && $task->status?->isOpen())
            ->sort(fn (StudyPlanTask $left, StudyPlanTask $right): int => $this->compare($left, $right, $today))
            ->values();

        $overdue = $openTasks
            ->filter(fn (StudyPlanTask $task): bool => $this->effectiveDate($task)?->lt($today))
            ->values();

        $todayTasks = $openTasks
            ->filter(fn (StudyPlanTask $task): bool => $this->effectiveDate($task)?->isSameDay($today) ?? false)
            ->values();

        $weekEnd = $today->copy()->endOfWeek();
        $thisWeek = $openTasks
            ->filter(function (StudyPlanTask $task) use ($today, $weekEnd): bool {
                $effectiveDate = $this->effectiveDate($task);

                return $effectiveDate !== null
                    && $effectiveDate->gt($today)
                    && $effectiveDate->lte($weekEnd);
            })
            ->values();

        $highPriority = $openTasks
            ->filter(fn (StudyPlanTask $task): bool => $task->priority_score >= 80)
            ->take(5)
            ->values();

        $completedRecently = $tasks
            ->filter(fn (StudyPlanTask $task): bool => $task->status === StudyPlanTaskStatusEnum::COMPLETED)
            ->sortByDesc('completed_at')
            ->take(5)
            ->values();

        return [
            'next_best_action' => $openTasks->first() ? $this->serialize($openTasks->first()) : null,
            'priority_recommendations' => $openTasks->take(5)->map(fn (StudyPlanTask $task): array => $this->serialize($task))->all(),
            'upcoming' => $openTasks->slice(5, 5)->map(fn (StudyPlanTask $task): array => $this->serialize($task))->all(),
            'agenda' => [
                'today' => $todayTasks->map(fn (StudyPlanTask $task): array => $this->serialize($task))->all(),
                'this_week' => $thisWeek->map(fn (StudyPlanTask $task): array => $this->serialize($task))->all(),
                'overdue' => $overdue->map(fn (StudyPlanTask $task): array => $this->serialize($task))->all(),
                'high_priority' => $highPriority->map(fn (StudyPlanTask $task): array => $this->serialize($task))->all(),
                'completed_recently' => $completedRecently->map(fn (StudyPlanTask $task): array => $this->serialize($task))->all(),
            ],
            'summary' => [
                'open_tasks' => $openTasks->count(),
                'completed_tasks' => $tasks->where('status', StudyPlanTaskStatusEnum::COMPLETED)->count(),
                'overdue_tasks' => $overdue->count(),
                'due_today' => $todayTasks->count(),
                'high_priority_tasks' => $highPriority->count(),
            ],
            'review_cadence' => [
                'upcoming_review_queue' => $openTasks
                    ->filter(fn (StudyPlanTask $task): bool => in_array($task->track, ['review', 'learn'], true))
                    ->take(6)
                    ->map(fn (StudyPlanTask $task): array => $this->serialize($task))
                    ->all(),
            ],
        ];
    }

    public function serialize(StudyPlanTask $task): array
    {
        $today = today();
        $effectiveDate = $this->effectiveDate($task);
        $status = $task->status ?? StudyPlanTaskStatusEnum::PENDING;

        return [
            'record_id' => $task->id,
            'id' => $task->task_key,
            'type' => $task->type,
            'track' => $task->track,
            'title' => $task->title,
            'description' => $task->description,
            'reason' => $task->reason,
            'action_label' => $task->action_label,
            'action_href' => $task->action_href,
            'priority_score' => (int) $task->priority_score,
            'priority_label' => $task->priority_label,
            'due_label' => $this->dueLabel($task, $effectiveDate, $today),
            'source' => $task->source,
            'status' => $status->value,
            'status_label' => $status->label(),
            'completion_source' => $task->completion_source,
            'completion_source_label' => $task->completion_source === 'auto'
                ? 'Selesai otomatis'
                : ($task->completion_source === 'manual' ? 'Selesai manual' : null),
            'resolved_activity_type' => $task->resolved_activity_type,
            'resolved_activity_id' => $task->resolved_activity_id,
            'recommended_for_date' => $task->recommended_for_date?->toDateString(),
            'scheduled_for_date' => $task->scheduled_for_date?->toDateString(),
            'snoozed_until' => $task->snoozed_until?->toDateString(),
            'completed_at' => $task->completed_at?->toIso8601String(),
            'is_overdue' => $effectiveDate?->lt($today) ?? false,
            'is_due_today' => $effectiveDate?->isSameDay($today) ?? false,
            'cadence' => [
                'label' => data_get($task->metadata, 'cadence.label'),
                'days' => data_get($task->metadata, 'cadence.days'),
                'reason' => data_get($task->metadata, 'cadence.reason'),
            ],
            'target' => data_get($task->metadata, 'target'),
            'history' => $task->relationLoaded('events')
                ? $task->events
                    ->take(5)
                    ->map(fn ($event): array => [
                        'event_type' => $event->event_type?->value,
                        'event_label' => $event->event_type?->label(),
                        'description' => $event->description,
                        'happened_at' => $event->happened_at?->toIso8601String(),
                        'impact' => data_get($event->event_payload, 'impact'),
                    ])
                    ->values()
                    ->all()
                : [],
            'outcome_impact' => data_get($task->metadata, 'last_auto_resolution.impact')
                ?? data_get($task->metadata, 'last_manual_impact'),
        ];
    }

    protected function compare(StudyPlanTask $left, StudyPlanTask $right, CarbonInterface $today): int
    {
        $leftTuple = $this->sortTuple($left, $today);
        $rightTuple = $this->sortTuple($right, $today);

        foreach ($leftTuple as $index => $value) {
            if ($value === $rightTuple[$index]) {
                continue;
            }

            return $value <=> $rightTuple[$index];
        }

        return 0;
    }

    protected function sortTuple(StudyPlanTask $task, CarbonInterface $today): array
    {
        $effectiveDate = $this->effectiveDate($task);

        return [
            $this->bucket($effectiveDate, $today),
            $effectiveDate?->timestamp ?? PHP_INT_MAX,
            -1 * (int) $task->priority_score,
            $task->id,
        ];
    }

    protected function bucket(?CarbonInterface $effectiveDate, CarbonInterface $today): int
    {
        if ($effectiveDate === null) {
            return 3;
        }

        if ($effectiveDate->lt($today)) {
            return 0;
        }

        if ($effectiveDate->isSameDay($today)) {
            return 1;
        }

        if ($effectiveDate->lte($today->copy()->endOfWeek())) {
            return 2;
        }

        return 3;
    }

    protected function effectiveDate(StudyPlanTask $task): ?CarbonInterface
    {
        return $task->snoozed_until
            ?? $task->scheduled_for_date
            ?? $task->recommended_for_date;
    }

    protected function dueLabel(StudyPlanTask $task, ?CarbonInterface $effectiveDate, CarbonInterface $today): string
    {
        if ($task->status === StudyPlanTaskStatusEnum::COMPLETED) {
            return $task->completed_at?->isSameDay($today)
                ? 'Selesai hari ini'
                : 'Selesai';
        }

        if ($effectiveDate === null) {
            return 'Tanpa jadwal';
        }

        if ($effectiveDate->lt($today)) {
            return 'Overdue '.$effectiveDate->diffInDays($today).' hari';
        }

        if ($effectiveDate->isSameDay($today)) {
            return 'Hari ini';
        }

        if ($effectiveDate->isSameDay($today->copy()->addDay())) {
            return 'Besok';
        }

        return 'Pada '.$effectiveDate->translatedFormat('d M');
    }
}
