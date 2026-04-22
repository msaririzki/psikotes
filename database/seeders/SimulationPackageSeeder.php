<?php

namespace Database\Seeders;

use App\Models\SimulationPackage;
use App\Models\Subtest;
use Illuminate\Database\Seeder;

class SimulationPackageSeeder extends Seeder
{
    public function run(): void
    {
        SimulationPackage::query()
            ->whereIn('slug', [
                'simulasi-dasar-akademik',
                'simulasi-fokus-ketelitian',
            ])
            ->update([
                'is_published' => false,
                'published_at' => null,
            ]);

        $packages = [
            [
                'title' => 'Simulasi Real Lengkap POLRI 2025',
                'slug' => 'simulasi-real-lengkap-polri-2025',
                'description' => 'Paket lengkap yang memakai semua soal pilihan ganda dari PDF POLRI 2025.',
                'instruction' => 'Paket penuh memakai semua soal pilihan ganda dari PDF. Kerjakan dari awal sampai akhir, tandai soal yang masih ragu, dan atur waktu sejak awal.',
                'duration_minutes' => 300,
                'sort_order' => 1,
                'is_published' => true,
                'subtests' => [
                    ['slug' => 'kemampuan-verbal', 'question_count' => 255, 'sort_order' => 1],
                    ['slug' => 'numerik', 'question_count' => 30, 'sort_order' => 2],
                    ['slug' => 'penghafalan-kata', 'question_count' => 15, 'sort_order' => 3],
                    ['slug' => 'hitung-cepat', 'question_count' => 65, 'sort_order' => 4],
                    ['slug' => 'figural', 'question_count' => 40, 'sort_order' => 5],
                    ['slug' => 'angka-hilang-dan-huruf-hilang', 'question_count' => 40, 'sort_order' => 6],
                ],
            ],
            [
                'title' => 'Latihan Sedang Akademik POLRI 2025',
                'slug' => 'simulasi-real-akademik-polri-2025',
                'description' => 'Paket sedang untuk latihan akademik bertahap sebelum masuk ke simulasi penuh.',
                'instruction' => 'Paket ini mengambil sebagian soal akademik. Cocok untuk latihan harian tanpa waktu sepanjang paket penuh.',
                'duration_minutes' => 120,
                'sort_order' => 2,
                'is_published' => true,
                'subtests' => [
                    ['slug' => 'kemampuan-verbal', 'question_count' => 70, 'sort_order' => 1],
                    ['slug' => 'numerik', 'question_count' => 30, 'sort_order' => 2],
                    ['slug' => 'penghafalan-kata', 'question_count' => 15, 'sort_order' => 3],
                    ['slug' => 'figural', 'question_count' => 20, 'sort_order' => 4],
                ],
            ],
            [
                'title' => 'Latihan Cepat Ketelitian POLRI 2025',
                'slug' => 'simulasi-real-kecepatan-polri-2025',
                'description' => 'Paket cepat untuk melatih hitungan dan ketelitian dalam waktu singkat.',
                'instruction' => 'Paket ini dibuat untuk latihan cepat. Jangan terlalu lama di satu soal; selesaikan dulu yang pasti lalu kembali ke soal yang ditandai.',
                'duration_minutes' => 35,
                'sort_order' => 3,
                'is_published' => true,
                'subtests' => [
                    ['slug' => 'hitung-cepat', 'question_count' => 30, 'sort_order' => 1],
                    ['slug' => 'angka-hilang-dan-huruf-hilang', 'question_count' => 20, 'sort_order' => 2],
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
