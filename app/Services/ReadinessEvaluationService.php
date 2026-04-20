<?php

namespace App\Services;

use App\Enums\AttemptModeEnum;
use App\Models\Attempt;
use App\Models\LearningModuleProgress;
use Illuminate\Support\Collection;

class ReadinessEvaluationService
{
    public function evaluate(Collection $subtestAnalytics, Collection $moduleProgresses, Collection $submittedAttempts): array
    {
        $completedModules = $moduleProgresses->where('status', 'completed')->count();
        $practiceAttempts = $submittedAttempts->where('mode', AttemptModeEnum::PRACTICE)->count();
        $simulationAttempts = $submittedAttempts->where('mode', AttemptModeEnum::SIMULATION)->count();

        $strongAreas = $subtestAnalytics->filter(
            fn (array $item): bool => ($item['average_accuracy'] ?? 0) >= 80 && $item['attempts_count'] > 0,
        );
        $weakAreas = $subtestAnalytics->filter(
            fn (array $item): bool => ($item['average_accuracy'] ?? 100) < 60 && $item['attempts_count'] > 0,
        );
        $decliningAreas = $subtestAnalytics->filter(
            fn (array $item): bool => ($item['trend']['direction'] ?? null) === 'declining',
        );

        if ($completedModules === 0 || $weakAreas->isNotEmpty()) {
            return [
                'state' => 'needs_foundation_review',
                'label' => 'Perlu review dasar',
                'headline' => 'Fokus utama masih di fondasi materi',
                'description' => 'Sebelum menaikkan intensitas latihan, rapikan dulu subtes terlemah dan modul dasar yang belum stabil.',
                'score' => 35,
                'signals' => $this->signals($completedModules, $practiceAttempts, $simulationAttempts, $strongAreas, $weakAreas, $decliningAreas),
            ];
        }

        if ($strongAreas->count() >= 2 && $completedModules >= 2 && $practiceAttempts >= 3 && $decliningAreas->isEmpty()) {
            return [
                'state' => 'ready_for_full_simulation',
                'label' => 'Siap simulasi penuh',
                'headline' => 'User sudah siap masuk ke ritme simulasi penuh',
                'description' => 'Beberapa subtes sudah stabil, modul dasar cukup, dan tidak ada weak area dominan yang menghambat.',
                'score' => 90,
                'signals' => $this->signals($completedModules, $practiceAttempts, $simulationAttempts, $strongAreas, $weakAreas, $decliningAreas),
            ];
        }

        if ($strongAreas->isNotEmpty() && $practiceAttempts >= 2) {
            return [
                'state' => 'ready_for_subtest_simulation',
                'label' => 'Siap simulasi subtes tertentu',
                'headline' => 'Beberapa area sudah cukup matang untuk tekanan yang lebih formal',
                'description' => 'Pertahankan subtes yang kuat dengan practice, lalu mulai arahkan energi ke simulation saat area lemah tidak lagi dominan.',
                'score' => 72,
                'signals' => $this->signals($completedModules, $practiceAttempts, $simulationAttempts, $strongAreas, $weakAreas, $decliningAreas),
            ];
        }

        return [
            'state' => 'ready_for_intermediate_practice',
            'label' => 'Siap latihan menengah',
            'headline' => 'Fondasi awal sudah ada, tetapi perlu penguatan ritme latihan',
            'description' => 'Tahap terbaik berikutnya adalah memperbanyak practice yang terarah sebelum menekan user ke simulation yang lebih formal.',
            'score' => 58,
            'signals' => $this->signals($completedModules, $practiceAttempts, $simulationAttempts, $strongAreas, $weakAreas, $decliningAreas),
        ];
    }

    protected function signals(
        int $completedModules,
        int $practiceAttempts,
        int $simulationAttempts,
        Collection $strongAreas,
        Collection $weakAreas,
        Collection $decliningAreas,
    ): array {
        return [
            [
                'label' => 'Modul selesai',
                'value' => $completedModules,
            ],
            [
                'label' => 'Practice submitted',
                'value' => $practiceAttempts,
            ],
            [
                'label' => 'Simulation submitted',
                'value' => $simulationAttempts,
            ],
            [
                'label' => 'Strong areas',
                'value' => $strongAreas->count(),
            ],
            [
                'label' => 'Weak areas',
                'value' => $weakAreas->count(),
            ],
            [
                'label' => 'Declining areas',
                'value' => $decliningAreas->count(),
            ],
        ];
    }
}
