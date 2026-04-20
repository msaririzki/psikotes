<?php

namespace App\Services;

class StudyCadenceService
{
    public function cadenceFor(array $task): array
    {
        return match ($task['source'] ?? null) {
            'weakest_area' => [
                'days' => 1,
                'label' => 'Cadence harian',
                'reason' => 'Weakest area perlu disentuh paling sering sampai fondasi stabil.',
            ],
            'stagnation', 'declining_subtest' => [
                'days' => 2,
                'label' => 'Cadence 2 hari',
                'reason' => 'Area stagnan atau menurun perlu review rapat tetapi tetap memberi jeda latihan.',
            ],
            'simulation_readiness' => [
                'days' => 3,
                'label' => 'Cadence 3 hari',
                'reason' => 'Area kuat cukup diberi ruang sebelum dinaikkan ke simulation.',
            ],
            'stale_module', 'unfinished_module' => [
                'days' => 2,
                'label' => 'Cadence 2 hari',
                'reason' => 'Modul yang tertinggal perlu segera disentuh lagi sebelum konteks hilang.',
            ],
            'simulation_review', 'recent_simulation' => [
                'days' => 1,
                'label' => 'Review cepat',
                'reason' => 'Hasil simulasi paling bernilai bila direview dekat dengan attempt terakhir.',
            ],
            default => [
                'days' => 3,
                'label' => 'Cadence mingguan ringan',
                'reason' => 'Task ini penting, tetapi tidak perlu disentuh setiap hari.',
            ],
        };
    }

    public function recommendedDate(array $task): string
    {
        if (isset($task['recommended_for_date'])) {
            return (string) $task['recommended_for_date'];
        }

        $cadence = $this->cadenceFor($task);

        return now()->addDays((int) $cadence['days'])->toDateString();
    }
}
