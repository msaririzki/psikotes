<?php

namespace Database\Seeders;

use App\Enums\ScoringTypeEnum;
use App\Models\Category;
use App\Models\Subtest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class DomainReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Tes Kecerdasan',
                'slug' => 'tes-kecerdasan',
                'description' => 'Kategori kemampuan verbal, numerik, dan figural dari bank soal Polri.',
                'sort_order' => 1,
                'subtests' => [
                    ['name' => 'Kemampuan Verbal', 'slug' => 'kemampuan-verbal', 'sort_order' => 1],
                    ['name' => 'Numerik', 'slug' => 'numerik', 'sort_order' => 2],
                    ['name' => 'Penghafalan Kata', 'slug' => 'penghafalan-kata', 'sort_order' => 3],
                    ['name' => 'Hitung Cepat', 'slug' => 'hitung-cepat', 'sort_order' => 4],
                    ['name' => 'Figural', 'slug' => 'figural', 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Tes Kecermatan dan Ketelitian',
                'slug' => 'tes-kecermatan-dan-ketelitian',
                'description' => 'Kategori tes fokus, ketelitian, dan kecepatan kerja.',
                'sort_order' => 2,
                'subtests' => [
                    ['name' => 'Koran Pauli', 'slug' => 'koran-pauli', 'sort_order' => 1],
                    ['name' => 'Angka Hilang & Huruf Hilang', 'slug' => 'angka-hilang-dan-huruf-hilang', 'sort_order' => 2],
                ],
            ],
            [
                'name' => 'Kepribadian',
                'slug' => 'kepribadian',
                'description' => 'Kategori penilaian kecenderungan perilaku dan profil personal.',
                'sort_order' => 3,
                'subtests' => [
                    ['name' => 'PAPI Kostick', 'slug' => 'papi-kostick', 'sort_order' => 1],
                    ['name' => 'Tes Kuisioner 2 Pilihan', 'slug' => 'tes-kuisioner-2-pilihan', 'sort_order' => 2],
                    ['name' => 'Tes Kuisioner 5 Pilihan', 'slug' => 'tes-kuisioner-5-pilihan', 'sort_order' => 3],
                    ['name' => 'Wartegg', 'slug' => 'tes-wartegg', 'sort_order' => 4],
                    ['name' => 'Tes Baum', 'slug' => 'tes-baum', 'sort_order' => 5],
                    ['name' => 'Draw a Person', 'slug' => 'draw-a-person', 'sort_order' => 6],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = Category::query()->updateOrCreate(
                ['slug' => $categoryData['slug']],
                Arr::except($categoryData, ['subtests']),
            );

            foreach ($categoryData['subtests'] as $subtestData) {
                Subtest::query()->updateOrCreate(
                    ['slug' => $subtestData['slug']],
                    [
                        ...$subtestData,
                        'category_id' => $category->id,
                        'scoring_type' => $category->slug === 'kepribadian'
                            ? ScoringTypeEnum::PERSONALITY_PROFILE
                            : ScoringTypeEnum::OBJECTIVE,
                        'default_duration_minutes' => null,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
