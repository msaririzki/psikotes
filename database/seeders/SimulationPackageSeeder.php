<?php

namespace Database\Seeders;

use App\Models\SimulationPackage;
use App\Models\Subtest;
use Illuminate\Database\Seeder;

class SimulationPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'title' => 'Simulasi Dasar Akademik',
                'slug' => 'simulasi-dasar-akademik',
                'description' => 'Paket simulasi dasar untuk membiasakan ritme CAT pada subtes akademik inti.',
                'instruction' => 'Kerjakan seluruh soal sesuai timer paket. Gunakan flag bila masih ragu dan review sebelum submit final.',
                'duration_minutes' => 60,
                'sort_order' => 1,
                'is_published' => true,
                'subtests' => [
                    ['slug' => 'kemampuan-verbal', 'question_count' => 10, 'sort_order' => 1],
                    ['slug' => 'numerik', 'question_count' => 10, 'sort_order' => 2],
                    ['slug' => 'figural', 'question_count' => 10, 'sort_order' => 3],
                ],
            ],
            [
                'title' => 'Simulasi Fokus Ketelitian',
                'slug' => 'simulasi-fokus-ketelitian',
                'description' => 'Paket simulasi untuk mengukur ketelitian dan kecepatan kerja secara lebih formal.',
                'instruction' => 'Jaga ritme kerja dan prioritaskan akurasi. Paket ini cocok dikerjakan setelah mode learn dan practice stabil.',
                'duration_minutes' => 45,
                'sort_order' => 2,
                'is_published' => true,
                'subtests' => [
                    ['slug' => 'hitung-cepat', 'question_count' => 10, 'sort_order' => 1],
                    ['slug' => 'koran-pauli', 'question_count' => 10, 'sort_order' => 2],
                    ['slug' => 'angka-hilang-dan-huruf-hilang', 'question_count' => 10, 'sort_order' => 3],
                ],
            ],
        ];

        foreach ($packages as $packageData) {
            $simulationPackage = SimulationPackage::query()->updateOrCreate(
                ['slug' => $packageData['slug']],
                [
                    'title' => $packageData['title'],
                    'description' => $packageData['description'],
                    'instruction' => $packageData['instruction'],
                    'duration_minutes' => $packageData['duration_minutes'],
                    'question_count' => collect($packageData['subtests'])->sum('question_count'),
                    'sort_order' => $packageData['sort_order'],
                    'is_published' => $packageData['is_published'],
                    'published_at' => $packageData['is_published'] ? now() : null,
                ],
            );

            $simulationPackage->packageSubtests()->delete();

            foreach ($packageData['subtests'] as $subtestData) {
                $subtest = Subtest::query()->where('slug', $subtestData['slug'])->first();

                if (! $subtest) {
                    continue;
                }

                $simulationPackage->packageSubtests()->create([
                    'subtest_id' => $subtest->id,
                    'question_count' => $subtestData['question_count'],
                    'sort_order' => $subtestData['sort_order'],
                ]);
            }
        }
    }
}
