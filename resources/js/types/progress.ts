export type HistorySummary = {
    learn: number;
    mini_quiz: number;
    practice: number;
    simulation: number;
};

export type HistoryTimelineItem = {
    id: string;
    kind: 'learn' | 'mini_quiz' | 'practice' | 'simulation';
    kind_label: string;
    title: string;
    subtitle: string | null;
    category_name: string | null;
    status_label: string;
    metric_label: string;
    description: string;
    occurred_at: string;
    href: string;
};

export type ProgressSummary = {
    learning_sessions: number;
    practice_attempts: number;
    simulation_attempts: number;
    average_score: number | null;
    average_accuracy: number | null;
    completed_modules: number;
    last_activity_at: string | null;
};

export type ProgressTrendPoint = {
    id: number;
    label: string;
    mode: string;
    mode_label: string;
    score: number;
    accuracy: number;
    occurred_at: string | null;
    context: string | null;
};

export type RecommendationItem = {
    type: string;
    tone: 'warning' | 'neutral' | 'success' | 'accent';
    headline: string;
    description: string;
    action_label: string;
    action_href: string;
    reason: string;
};

export type SubtestAnalyticsItem = {
    subtest_id: number;
    subtest_name: string;
    subtest_slug: string;
    category_name: string | null;
    attempts_count: number;
    mini_quiz_attempts: number;
    practice_attempts: number;
    simulation_attempts: number;
    average_score: number | null;
    average_accuracy: number | null;
    average_duration_seconds: number | null;
    correct_answers: number;
    wrong_answers: number;
    blank_answers: number;
    blank_rate: number;
    module_progress_count: number;
    completed_modules: number;
    in_progress_modules: number;
    published_modules_count: number;
    completion_rate: number;
    last_activity_at: string | null;
    recent_scores: number[];
    trend: {
        delta: number;
        direction: 'improving' | 'declining' | 'stable';
    };
    status: 'strong' | 'weak' | 'developing' | 'not_enough_data';
};


