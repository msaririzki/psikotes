export type BelajarProgres = {
    status: 'not_started' | 'in_progress' | 'completed';
    label: string;
    last_quiz_score: number | null;
    quiz_attempts_count: number;
    completed_at: string | null;
    last_viewed_at: string | null;
};

export type BelajarSummary = {
    categories: number;
    subtests: number;
    modules: number;
    completed: number;
    in_progress: number;
    not_started: number;
    completion_rate: number;
};

export type BelajarRecentMiniQuiz = {
    id: number;
    learning_module: string | null;
    learning_module_slug: string | null;
    score_total: number | null;
    accuracy: number | null;
    submitted_at: string | null;
};

