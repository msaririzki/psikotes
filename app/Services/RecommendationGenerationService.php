<?php

namespace App\Services;

use App\Enums\ModuleLevelEnum;
use App\Models\LearningModule;
use App\Models\LearningModuleProgress;
use App\Models\User;

class RecommendationGenerationService
{
    public function generate(User $user, array $subtestAnalytics): array
    {
        $recommendations = [];

        $items = collect($subtestAnalytics['items'] ?? []);

        $weakArea = $items
            ->filter(fn (array $item): bool => $item['attempts_count'] > 0)
            ->sortBy('average_accuracy')
            ->first();

        if ($weakArea && ($weakArea['average_accuracy'] ?? 100) < 60) {
            $basicModule = LearningModule::query()
                ->where('subtest_id', $weakArea['subtest_id'])
                ->where('is_published', true)
                ->where('level', ModuleLevelEnum::BASIC)
                ->orderBy('published_at')
                ->orderBy('title')
                ->first();

            $recommendations[] = [
                'type' => 'recovery',
                'tone' => 'warning',
                'headline' => 'Rapikan ulang fondasi '.$weakArea['subtest_name'],
                'description' => 'Akurasi di subtes ini masih rendah. Mulai dari modul dasar lalu ulangi mini quiz atau practice dengan ritme yang lebih pelan.',
                'action_label' => $basicModule ? 'Buka modul dasar' : 'Buka subtes belajar',
                'action_href' => $basicModule
                    ? '/learn/modules/'.$basicModule->slug
                    : '/learn',
                'reason' => 'Akurasi rata-rata '.$weakArea['average_accuracy'].'% dari '.$weakArea['attempts_count'].' attempt.',
            ];
        }

        $stagnantArea = $items->first(function (array $item): bool {
            return $item['attempts_count'] >= 3
                && ($item['average_accuracy'] ?? 100) < 75
                && ($item['trend']['direction'] ?? null) === 'stable';
        });

        if ($stagnantArea) {
            $recommendations[] = [
                'type' => 'stagnation',
                'tone' => 'neutral',
                'headline' => 'Turunkan beban latihan di '.$stagnantArea['subtest_name'],
                'description' => 'Performa terlihat stagnan. Ulangi practice dengan target soal lebih pendek atau review modul inti sebelum naik lagi.',
                'action_label' => 'Buka practice subtes',
                'action_href' => '/practice/subtests/'.$stagnantArea['subtest_slug'],
                'reason' => 'Tiga attempt terakhir belum menunjukkan kenaikan yang berarti.',
            ];
        }

        $improvingArea = $items->first(function (array $item): bool {
            return $item['attempts_count'] >= 2
                && ($item['average_accuracy'] ?? 0) >= 75
                && ($item['trend']['direction'] ?? null) === 'improving';
        });

        if ($improvingArea) {
            $recommendations[] = [
                'type' => 'growth',
                'tone' => 'success',
                'headline' => 'Naikkan tantangan di '.$improvingArea['subtest_name'],
                'description' => 'Performa sudah naik stabil. Lanjutkan ke practice yang lebih serius atau masuk ke simulation untuk menguji ritme penuh.',
                'action_label' => 'Buka simulations',
                'action_href' => '/simulations',
                'reason' => 'Tren performa meningkat dengan akurasi rata-rata '.$improvingArea['average_accuracy'].'%.',
            ];
        }

        $staleProgress = LearningModuleProgress::query()
            ->where('user_id', $user->id)
            ->whereNotNull('last_viewed_at')
            ->where('last_viewed_at', '<=', now()->subDays(10))
            ->with('learningModule.subtest')
            ->latest('last_viewed_at')
            ->first();

        if ($staleProgress?->learningModule?->is_published) {
            $recommendations[] = [
                'type' => 'review',
                'tone' => 'accent',
                'headline' => 'Review ulang materi '.$staleProgress->learningModule->title,
                'description' => 'Modul ini sudah lama tidak dibuka. Review singkat akan membantu menjaga memori kerja sebelum attempt berikutnya.',
                'action_label' => 'Buka modul',
                'action_href' => '/learn/modules/'.$staleProgress->learningModule->slug,
                'reason' => 'Terakhir dibuka '.$staleProgress->last_viewed_at?->diffForHumans().'.',
            ];
        }

        return collect($recommendations)
            ->unique('headline')
            ->take(4)
            ->values()
            ->all();
    }
}
