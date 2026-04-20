export type SimulationRiwayatItem = {
    id: number;
    package_title: string | null;
    score_total: number | null;
    accuracy: number | null;
    correct_answers: number;
    wrong_answers: number;
    blank_answers: number;
    duration_seconds: number;
    submitted_at: string | null;
};

export type SimulationSummary = {
    packages: number;
    attempts: number;
    best_score: number | null;
    average_accuracy: number | null;
};

