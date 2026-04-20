export type ReadinessSignal = {
    label: string;
    value: number;
};

export type ReadinessSummary = {
    state: string;
    label: string;
    headline: string;
    description: string;
    score: number;
    signals: ReadinessSignal[];
};

export type StudyTask = {
    record_id: number;
    id: string;
    type: string;
    track: 'learn' | 'practice' | 'simulation' | 'review';
    title: string;
    description: string;
    reason: string;
    action_label: string;
    action_href: string;
    priority_score: number;
    priority_label?: string;
    due_label: string;
    source: string;
    status: 'pending' | 'snoozed' | 'rescheduled' | 'completed';
    status_label: string;
    completion_source: 'manual' | 'auto' | null;
    completion_source_label: string | null;
    resolved_activity_type: string | null;
    resolved_activity_id: number | null;
    recommended_for_date: string | null;
    scheduled_for_date: string | null;
    snoozed_until: string | null;
    completed_at: string | null;
    is_overdue: boolean;
    is_due_today: boolean;
    cadence: {
        label: string | null;
        days: number | null;
        reason: string | null;
    };
    target: Record<string, unknown> | null;
    history: Array<{
        event_type: string | null;
        event_label: string | null;
        description: string | null;
        happened_at: string | null;
        impact?: {
            readiness_changed?: boolean;
            next_best_action_changed?: boolean;
        } | null;
    }>;
    outcome_impact?: {
        readiness_changed?: boolean;
        next_best_action_changed?: boolean;
    } | null;
};

export type StudyGoalTarget = {
    label: string;
    current: number;
    target: number;
};

export type StudyGoalAlignment = {
    score: number;
    label: string;
    description: string;
    supporting_open_tasks: number;
    supporting_completed_tasks: number;
    supporting_due_today: number;
    next_aligned_action: StudyTask | null;
};

export type StudyGoal = {
    id: number;
    key: string;
    period_type: 'weekly' | 'monthly';
    period_label: string;
    goal_type: string;
    title: string;
    description: string;
    rationale: string;
    window_label: string;
    status: 'on_track' | 'off_track' | 'completed';
    status_label: string;
    progress: number;
    elapsed_progress: number;
    pace_label: string;
    focus: {
        subtest_name: string | null;
        subtest_slug: string | null;
        tracks: string[];
        target_readiness_label: string | null;
    };
    targets: StudyGoalTarget[];
    outcomes: {
        readiness_score_delta: number;
        accuracy_delta: number | null;
        baseline_readiness_label: string | null;
        current_readiness_label: string | null;
        readiness_changed: boolean;
    };
    alignment: StudyGoalAlignment;
    milestone: {
        id: string;
        label: string;
        progress: number;
        state: 'starting' | 'in_progress' | 'completed';
    } | null;
};

export type ReadinessMilestoneSignal = {
    label: string;
    current: number;
    target: number;
};

export type ReadinessMilestone = {
    id: string;
    label: string;
    progress: number;
    state: 'starting' | 'in_progress' | 'completed';
    description: string;
    signals: ReadinessMilestoneSignal[];
};

export type ReadinessSubtestProgress = {
    subtest_name: string;
    subtest_slug: string;
    label: string;
    progress: number;
    state: 'foundation' | 'practice_ready' | 'simulation_ready';
    reason: string;
};

export type ReadinessProgress = {
    summary: {
        current_state: string;
        current_label: string;
        readiness_score: number;
        target_label: string;
        execution_completion_rate: number;
        completed_tasks: number;
        open_tasks: number;
    };
    milestones: ReadinessMilestone[];
    subtests: ReadinessSubtestProgress[];
};

export type StudyPlanAgenda = {
    today: StudyTask[];
    this_week: StudyTask[];
    overdue: StudyTask[];
    high_priority: StudyTask[];
    completed_recently: StudyTask[];
};

export type StudyPlanPayload = {
    readiness: ReadinessSummary;
    readiness_progress: ReadinessProgress;
    goal_tracking: {
        summary: {
            on_track_goals: number;
            off_track_goals: number;
            completed_goals: number;
            primary_focus: string | null;
            readiness_target: string | null;
        };
        primary_goal: StudyGoal | null;
        active_goals: StudyGoal[];
    };
    next_best_action: StudyTask | null;
    priority_recommendations: StudyTask[];
    review_queue: StudyTask[];
    plan_sections: {
        immediate: StudyTask[];
        upcoming: StudyTask[];
    };
    agenda: StudyPlanAgenda;
    execution_summary: {
        open_tasks: number;
        completed_tasks: number;
        overdue_tasks: number;
        due_today: number;
        high_priority_tasks: number;
    };
    review_cadence: {
        upcoming_review_queue: StudyTask[];
    };
    transparency: string[];
};


