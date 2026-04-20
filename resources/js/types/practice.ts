export type LatihanAnalytics = {
    attempts_count: number;
    best_score: number | null;
    average_accuracy: number | null;
    latest_score: number | null;
    latest_accuracy: number | null;
    last_submitted_at: string | null;
};

export type LatihanRiwayatItem = {
    id: number;
    subtest: {
        name: string | null;
        slug: string | null;
    };
    score_total: number | null;
    accuracy: number | null;
    correct_answers: number;
    wrong_answers: number;
    blank_answers: number;
    duration_seconds: number;
    submitted_at: string | null;
    configuration: {
        difficulty_label: string;
        question_count: number;
        timer_minutes: number | null;
    };
};

export type LatihanSummary = {
    subtests: number;
    practice_attempts: number;
    best_score: number | null;
    average_accuracy: number | null;
    in_progress_sessions: number;
};

