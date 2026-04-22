<?php

namespace Database\Seeders;

use App\Enums\DifficultyEnum;
use App\Enums\ModuleLevelEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Models\LearningModule;
use App\Models\Subtest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $authorId = User::query()
            ->where('email', 'admin@prikotes.test')
            ->orWhere('email', 'superadmin@prikotes.test')
            ->value('id');

        foreach ($this->blueprints() as $slug => $blueprint) {
            $subtest = Subtest::query()->where('slug', $slug)->first();

            if (! $subtest) {
                continue;
            }

            foreach ($blueprint['modules'] as $moduleData) {
                LearningModule::query()->updateOrCreate(
                    ['slug' => $moduleData['slug']],
                    [
                        'subtest_id' => $subtest->id,
                        'title' => $moduleData['title'],
                        'summary' => $moduleData['summary'],
                        'content' => $moduleData['content'],
                        'tips' => $moduleData['tips'],
                        'tricks' => $moduleData['tricks'],
                        'level' => $moduleData['level'],
                        'estimated_minutes' => $moduleData['estimated_minutes'],
                        'is_published' => true,
                        'published_at' => now(),
                        'created_by' => $authorId,
                        'updated_by' => $authorId,
                    ],
                );
            }

        }
    }

    protected function blueprints(): array
    {
        return [
            'kemampuan-verbal' => [
                'modules' => [
                    $this->module(
                        'modul-verbal-fondasi',
                        'Fondasi Verbal: Sinonim, Antonim, dan Analogi',
                        'Mulai dari pola dasar soal verbal yang paling sering muncul di psikotes Polri.',
                        'Pada modul ini kamu belajar mengenali hubungan makna kata, arah lawan kata, dan pola analogi sederhana. Fokus utamanya adalah membaca kata kunci, menguji konteks, lalu menyingkirkan opsi yang terlalu jauh maknanya.',
                        'Mulai dari kata yang paling familiar, lalu cocokkan konteksnya sebelum menilai opsi lain.',
                        'Jika dua opsi terasa mirip, cari pasangan yang hubungannya paling konsisten dengan stem soal.',
                        ModuleLevelEnum::BASIC,
                        25,
                    ),
                    $this->module(
                        'modul-verbal-kecepatan',
                        'Strategi Verbal Cepat dan Akurat',
                        'Naikkan ritme menjawab soal verbal tanpa kehilangan akurasi.',
                        'Modul ini berfokus pada cara membaca cepat, menemukan relasi kata, dan menahan diri dari opsi jebakan yang tampak benar tetapi tidak paling tepat.',
                        'Tandai kata hubungan seperti sebab-akibat, bagian-keseluruhan, atau fungsi-benda.',
                        'Gunakan eliminasi agresif saat ada dua opsi yang jelas di luar pola hubungan.',
                        ModuleLevelEnum::INTERMEDIATE,
                        20,
                    ),
                ],
                'questions' => $this->objectiveQuestionSet('VRB', [
                    ['question' => 'Sinonim kata "cermat" adalah ...', 'options' => ['teliti', 'ceroboh', 'bimbang', 'lambat'], 'correct' => 0, 'explanation' => 'Cermat paling dekat maknanya dengan teliti.'],
                    ['question' => 'Antonim kata "optimis" adalah ...', 'options' => ['percaya diri', 'ragu', 'tekun', 'waspada'], 'correct' => 1, 'explanation' => 'Optimis berlawanan dengan ragu atau pesimis.'],
                    ['question' => 'Analogi yang tepat: dokter : pasien = guru : ...', 'options' => ['kelas', 'murid', 'buku', 'sekolah'], 'correct' => 1, 'explanation' => 'Dokter menangani pasien, guru menangani murid.'],
                    ['question' => 'Sinonim kata "sigap" adalah ...', 'options' => ['cepat tanggap', 'mudah lelah', 'kurang fokus', 'banyak bicara'], 'correct' => 0, 'explanation' => 'Sigap berarti cepat tanggap.'],
                    ['question' => 'Antonim kata "jujur" adalah ...', 'options' => ['setia', 'ramah', 'bohong', 'tegas'], 'correct' => 2, 'explanation' => 'Jujur berlawanan dengan bohong atau dusta.'],
                    ['question' => 'Analogi yang tepat: kompas : arah = jam : ...', 'options' => ['suara', 'waktu', 'cahaya', 'langkah'], 'correct' => 1, 'explanation' => 'Kompas menunjukkan arah, jam menunjukkan waktu.'],
                    ['question' => 'Sinonim kata "gigih" adalah ...', 'options' => ['ulet', 'pasif', 'malas', 'rapuh'], 'correct' => 0, 'explanation' => 'Gigih memiliki makna ulet dan pantang menyerah.'],
                    ['question' => 'Antonim kata "stabil" adalah ...', 'options' => ['tetap', 'goyah', 'rapi', 'jelas'], 'correct' => 1, 'explanation' => 'Stabil berlawanan dengan goyah atau tidak mantap.'],
                    ['question' => 'Analogi yang tepat: bensin : kendaraan = listrik : ...', 'options' => ['lampu', 'jalan', 'asap', 'mesin'], 'correct' => 0, 'explanation' => 'Bensin menggerakkan kendaraan, listrik menyalakan lampu.'],
                    ['question' => 'Sinonim kata "mandiri" adalah ...', 'options' => ['bergantung', 'otonom', 'terbatas', 'terpaksa'], 'correct' => 1, 'explanation' => 'Mandiri paling dekat maknanya dengan otonom atau berdiri sendiri.'],
                    ['question' => 'Antonim kata "hemat" adalah ...', 'options' => ['boros', 'pelit', 'cukup', 'murah'], 'correct' => 0, 'explanation' => 'Hemat berlawanan dengan boros.'],
                    ['question' => 'Analogi yang tepat: peta : wilayah = jadwal : ...', 'options' => ['waktu', 'kursi', 'buku', 'petugas'], 'correct' => 0, 'explanation' => 'Peta menggambarkan wilayah, jadwal menggambarkan waktu.'],
                ]),
            ],
            'numerik' => [
                'modules' => [
                    $this->module(
                        'modul-numerik-dasar',
                        'Numerik Dasar: Pola, Operasi, dan Logika Angka',
                        'Bangun fondasi numerik mulai dari deret, aritmetika sederhana, dan hubungan kuantitatif.',
                        'Modul ini membantu user pemula mengenali pola deret, operasi campuran, dan cara menjaga akurasi saat berhitung di bawah tekanan.',
                        'Tuliskan selisih atau rasio antar angka sebelum menebak jawabannya.',
                        'Jika deret terasa acak, cek kemungkinan pola selang-seling atau operasi berulang.',
                        ModuleLevelEnum::BASIC,
                        30,
                    ),
                    $this->module(
                        'modul-numerik-strategi',
                        'Strategi Numerik untuk Kecepatan Kerja',
                        'Pelajari cara memangkas langkah hitung tanpa mengorbankan jawaban.',
                        'Modul lanjutan ini menekankan pembulatan cerdas, eliminasi opsi, dan pengenalan pola cepat pada soal numerik.',
                        'Gunakan estimasi cepat untuk membuang opsi yang mustahil.',
                        'Simpan perhitungan detail hanya untuk dua opsi yang benar-benar dekat.',
                        ModuleLevelEnum::INTERMEDIATE,
                        25,
                    ),
                ],
                'questions' => $this->objectiveQuestionSet('NUM', [
                    ['question' => 'Lanjutkan deret: 2, 4, 8, 16, ...', 'options' => ['18', '24', '32', '34'], 'correct' => 2, 'explanation' => 'Setiap angka dikali 2.'],
                    ['question' => 'Hasil dari 15 + 27 adalah ...', 'options' => ['40', '41', '42', '43'], 'correct' => 2, 'explanation' => '15 + 27 = 42.'],
                    ['question' => 'Lanjutkan deret: 3, 6, 9, 12, ...', 'options' => ['14', '15', '16', '18'], 'correct' => 1, 'explanation' => 'Pola bertambah 3.'],
                    ['question' => 'Hasil dari 48 : 6 adalah ...', 'options' => ['6', '7', '8', '9'], 'correct' => 2, 'explanation' => '48 dibagi 6 = 8.'],
                    ['question' => 'Lanjutkan deret: 5, 10, 20, 40, ...', 'options' => ['45', '60', '70', '80'], 'correct' => 3, 'explanation' => 'Setiap angka dikali 2.'],
                    ['question' => 'Hasil dari 7 x 8 adalah ...', 'options' => ['54', '56', '58', '64'], 'correct' => 1, 'explanation' => '7 x 8 = 56.'],
                    ['question' => 'Lanjutkan deret: 100, 90, 80, 70, ...', 'options' => ['65', '60', '55', '50'], 'correct' => 1, 'explanation' => 'Pola berkurang 10.'],
                    ['question' => 'Hasil dari 81 - 29 adalah ...', 'options' => ['50', '51', '52', '53'], 'correct' => 2, 'explanation' => '81 - 29 = 52.'],
                    ['question' => 'Lanjutkan deret: 1, 4, 9, 16, ...', 'options' => ['20', '24', '25', '36'], 'correct' => 2, 'explanation' => 'Deret kuadrat 1^2, 2^2, 3^2, 4^2, 5^2.'],
                    ['question' => 'Jika 3 buku seharga 24.000, maka harga 1 buku adalah ...', 'options' => ['6.000', '7.000', '8.000', '9.000'], 'correct' => 2, 'explanation' => '24.000 dibagi 3 = 8.000.'],
                    ['question' => 'Lanjutkan deret: 12, 15, 19, 24, ...', 'options' => ['28', '29', '30', '31'], 'correct' => 2, 'explanation' => 'Selisihnya +3, +4, +5, jadi berikutnya +6.'],
                    ['question' => 'Hasil dari 144 : 12 adalah ...', 'options' => ['10', '11', '12', '13'], 'correct' => 2, 'explanation' => '144 dibagi 12 = 12.'],
                ]),
            ],
            'figural' => [
                'modules' => [
                    $this->module(
                        'modul-figural-pola',
                        'Figural Dasar: Pola Bentuk dan Arah',
                        'Pahami cara membaca pola perubahan gambar secara sistematis.',
                        'Modul ini menekankan observasi arah rotasi, jumlah sisi, posisi titik, dan aturan perubahan sederhana pada gambar.',
                        'Cari satu atribut yang berubah konsisten, seperti rotasi atau penambahan sisi.',
                        'Jangan periksa semua detail sekaligus; fokus ke dua ciri paling menonjol lebih dulu.',
                        ModuleLevelEnum::BASIC,
                        25,
                    ),
                    $this->module(
                        'modul-figural-review',
                        'Review Figural untuk Kecepatan Seleksi',
                        'Rapikan cara membedakan pola utama dan pola pengecoh pada soal figural.',
                        'Latihan ini membantu user lebih cepat mengenali transformasi bentuk tanpa terpancing detail yang tidak relevan.',
                        'Bandingkan posisi, arah, dan jumlah elemen secara berurutan.',
                        'Jika pilihan tampak mirip, cek transformasi terakhir yang paling sering menentukan jawaban.',
                        ModuleLevelEnum::INTERMEDIATE,
                        20,
                    ),
                ],
                'questions' => $this->objectiveQuestionSet('FIG', [
                    ['question' => 'Jika sebuah segitiga diputar 90 derajat ke kanan, arah puncaknya akan ...', 'options' => ['tetap ke atas', 'mengarah ke kanan', 'mengarah ke kiri', 'mengarah ke bawah'], 'correct' => 1, 'explanation' => 'Rotasi 90 derajat ke kanan membuat puncak mengarah ke kanan.'],
                    ['question' => 'Urutan pola: lingkaran, setengah lingkaran, seperempat lingkaran, maka bentuk berikutnya adalah ...', 'options' => ['lingkaran penuh', 'seperdelapan lingkaran', 'segitiga', 'persegi'], 'correct' => 1, 'explanation' => 'Polanya membagi luasan lingkaran menjadi setengah lagi.'],
                    ['question' => 'Jika jumlah sisi bertambah satu tiap langkah: segitiga, persegi, pentagon, maka bentuk berikutnya adalah ...', 'options' => ['heksagon', 'oktagon', 'lingkaran', 'trapesium'], 'correct' => 0, 'explanation' => '3 sisi, 4 sisi, 5 sisi, jadi berikutnya 6 sisi.'],
                    ['question' => 'Sebuah panah awalnya ke atas lalu berputar 180 derajat, hasilnya ...', 'options' => ['ke kiri', 'ke kanan', 'ke bawah', 'tetap ke atas'], 'correct' => 2, 'explanation' => 'Rotasi 180 derajat membalik arah menjadi ke bawah.'],
                    ['question' => 'Pola titik bertambah 2 tiap langkah: 1, 3, 5, maka berikutnya ...', 'options' => ['6', '7', '8', '9'], 'correct' => 1, 'explanation' => 'Deret bilangan ganjil bertambah 2.'],
                    ['question' => 'Jika sebuah persegi diputar 45 derajat, tampilannya paling mirip ...', 'options' => ['belah ketupat', 'segitiga', 'trapesium', 'lingkaran'], 'correct' => 0, 'explanation' => 'Persegi yang diputar 45 derajat tampak seperti belah ketupat.'],
                    ['question' => 'Pola warna berganti hitam-putih-hitam-putih, maka elemen berikutnya adalah ...', 'options' => ['abu-abu', 'hitam', 'putih', 'transparan'], 'correct' => 1, 'explanation' => 'Setelah putih, pola kembali ke hitam.'],
                    ['question' => 'Jika sebuah garis horizontal dibalik secara vertikal, maka posisinya ...', 'options' => ['tetap horizontal', 'menjadi diagonal', 'menjadi vertikal', 'hilang'], 'correct' => 0, 'explanation' => 'Garis horizontal tetap horizontal setelah refleksi vertikal.'],
                    ['question' => 'Pola jumlah sudut: 3, 4, 5, 6, maka berikutnya ...', 'options' => ['6', '7', '8', '9'], 'correct' => 1, 'explanation' => 'Jumlah sudut bertambah satu setiap langkah.'],
                    ['question' => 'Jika titik berpindah searah jarum jam di empat sudut persegi, maka setelah kanan bawah akan ke ...', 'options' => ['kanan atas', 'kiri bawah', 'kiri atas', 'tengah'], 'correct' => 1, 'explanation' => 'Urutan sudut searah jarum jam adalah kiri atas, kanan atas, kanan bawah, kiri bawah.'],
                    ['question' => 'Bentuk yang tersusun dari dua segitiga sama besar paling mungkin menjadi ...', 'options' => ['persegi panjang', 'belah ketupat', 'lingkaran', 'trapesium siku'], 'correct' => 1, 'explanation' => 'Dua segitiga yang disatukan simetris sering membentuk belah ketupat.'],
                    ['question' => 'Jika pola garis bertambah dari 1, 2, 4, 8, maka berikutnya ...', 'options' => ['10', '12', '16', '18'], 'correct' => 2, 'explanation' => 'Pola jumlah garis dikali dua setiap langkah.'],
                ]),
            ],
            'hitung-cepat' => [
                'modules' => [
                    $this->module(
                        'modul-hitung-cepat-dasar',
                        'Hitung Cepat Dasar untuk Akurasi Waktu',
                        'Bangun ritme berhitung cepat dengan operasi yang paling sering dipakai.',
                        'Modul ini menekankan pengelompokan angka, pembulatan cepat, dan menjaga fokus pada soal hitung singkat.',
                        'Sederhanakan operasi sebelum menghitung detail.',
                        'Cari pasangan angka yang mudah dijumlah atau dikali terlebih dahulu.',
                        ModuleLevelEnum::BASIC,
                        20,
                    ),
                    $this->module(
                        'modul-hitung-cepat-lanjut',
                        'Hitung Cepat Lanjut untuk Ritme Seleksi',
                        'Latih respons cepat pada operasi campuran dan pengambilan keputusan numerik singkat.',
                        'Setelah fondasi dasar kuat, modul ini membantu user menjaga akurasi saat waktu semakin ketat.',
                        'Gunakan estimasi untuk mengeliminasi opsi sebelum hitung lengkap.',
                        'Jangan menahan satu soal terlalu lama; jaga ritme keseluruhan.',
                        ModuleLevelEnum::INTERMEDIATE,
                        20,
                    ),
                ],
                'questions' => $this->objectiveQuestionSet('HCP', [
                    ['question' => '25 + 17 = ...', 'options' => ['40', '41', '42', '43'], 'correct' => 2, 'explanation' => '25 + 17 = 42.'],
                    ['question' => '48 - 19 = ...', 'options' => ['28', '29', '30', '31'], 'correct' => 1, 'explanation' => '48 - 19 = 29.'],
                    ['question' => '12 x 6 = ...', 'options' => ['68', '70', '72', '74'], 'correct' => 2, 'explanation' => '12 x 6 = 72.'],
                    ['question' => '81 : 9 = ...', 'options' => ['7', '8', '9', '10'], 'correct' => 2, 'explanation' => '81 dibagi 9 = 9.'],
                    ['question' => '36 + 24 = ...', 'options' => ['58', '59', '60', '61'], 'correct' => 2, 'explanation' => '36 + 24 = 60.'],
                    ['question' => '14 x 5 = ...', 'options' => ['65', '68', '70', '72'], 'correct' => 2, 'explanation' => '14 x 5 = 70.'],
                    ['question' => '90 - 37 = ...', 'options' => ['51', '52', '53', '54'], 'correct' => 2, 'explanation' => '90 - 37 = 53.'],
                    ['question' => '63 : 7 = ...', 'options' => ['7', '8', '9', '10'], 'correct' => 2, 'explanation' => '63 dibagi 7 = 9.'],
                    ['question' => '18 + 27 = ...', 'options' => ['43', '44', '45', '46'], 'correct' => 2, 'explanation' => '18 + 27 = 45.'],
                    ['question' => '11 x 11 = ...', 'options' => ['111', '121', '131', '141'], 'correct' => 1, 'explanation' => '11 x 11 = 121.'],
                    ['question' => '72 - 28 = ...', 'options' => ['42', '43', '44', '45'], 'correct' => 2, 'explanation' => '72 - 28 = 44.'],
                    ['question' => '96 : 12 = ...', 'options' => ['6', '7', '8', '9'], 'correct' => 2, 'explanation' => '96 dibagi 12 = 8.'],
                ]),
            ],
            'koran-pauli' => [
                'modules' => [
                    $this->module(
                        'modul-pauli-fondasi',
                        'Koran Pauli: Fondasi Ritme dan Ketelitian',
                        'Pelajari pola kerja Pauli dari ritme penjumlahan sampai cara menjaga fokus.',
                        'Modul ini menjelaskan bagaimana membaca deret angka vertikal, menjumlahkan cepat, dan menjaga konsentrasi tanpa berhenti terlalu lama.',
                        'Jaga ritme stabil, bukan sekadar cepat di awal.',
                        'Jika salah satu angka sulit dibaca, segera pindah ke pasangan berikutnya agar ritme tidak terputus.',
                        ModuleLevelEnum::BASIC,
                        20,
                    ),
                    $this->module(
                        'modul-pauli-review',
                        'Koran Pauli: Strategi Review Hasil',
                        'Pahami cara mengevaluasi kesalahan dan blank area pada latihan ketelitian.',
                        'Modul lanjutan ini berfokus pada konsistensi, bukan hanya kecepatan, agar performa Pauli lebih stabil.',
                        'Bandingkan area yang salah dengan ritme kerja saat itu.',
                        'Latihan pendek berulang lebih efektif daripada satu sesi terlalu panjang.',
                        ModuleLevelEnum::INTERMEDIATE,
                        18,
                    ),
                ],
                'questions' => $this->objectiveQuestionSet('PAU', [
                    ['question' => 'Dalam pola Pauli, 7 + 5 menghasilkan digit ...', 'options' => ['1', '2', '3', '4'], 'correct' => 1, 'explanation' => 'Pada Pauli ditulis digit satuan, 7 + 5 = 12 sehingga yang dicatat 2.'],
                    ['question' => 'Dalam pola Pauli, 8 + 9 menghasilkan digit ...', 'options' => ['5', '6', '7', '8'], 'correct' => 2, 'explanation' => '8 + 9 = 17, digit satuannya 7.'],
                    ['question' => 'Dalam pola Pauli, 4 + 3 menghasilkan digit ...', 'options' => ['5', '6', '7', '8'], 'correct' => 2, 'explanation' => '4 + 3 = 7.'],
                    ['question' => 'Dalam pola Pauli, 6 + 8 menghasilkan digit ...', 'options' => ['2', '3', '4', '5'], 'correct' => 2, 'explanation' => '6 + 8 = 14, digit satuannya 4.'],
                    ['question' => 'Dalam pola Pauli, 9 + 9 menghasilkan digit ...', 'options' => ['6', '7', '8', '9'], 'correct' => 2, 'explanation' => '9 + 9 = 18, digit satuannya 8.'],
                    ['question' => 'Dalam pola Pauli, 2 + 5 menghasilkan digit ...', 'options' => ['5', '6', '7', '8'], 'correct' => 2, 'explanation' => '2 + 5 = 7.'],
                    ['question' => 'Dalam pola Pauli, 3 + 7 menghasilkan digit ...', 'options' => ['0', '1', '2', '3'], 'correct' => 0, 'explanation' => '3 + 7 = 10, digit satuannya 0.'],
                    ['question' => 'Dalam pola Pauli, 5 + 6 menghasilkan digit ...', 'options' => ['0', '1', '2', '3'], 'correct' => 1, 'explanation' => '5 + 6 = 11, digit satuannya 1.'],
                    ['question' => 'Dalam pola Pauli, 1 + 8 menghasilkan digit ...', 'options' => ['7', '8', '9', '0'], 'correct' => 2, 'explanation' => '1 + 8 = 9.'],
                    ['question' => 'Dalam pola Pauli, 7 + 7 menghasilkan digit ...', 'options' => ['2', '3', '4', '5'], 'correct' => 2, 'explanation' => '7 + 7 = 14, digit satuannya 4.'],
                    ['question' => 'Dalam pola Pauli, 6 + 3 menghasilkan digit ...', 'options' => ['7', '8', '9', '0'], 'correct' => 2, 'explanation' => '6 + 3 = 9.'],
                    ['question' => 'Dalam pola Pauli, 8 + 4 menghasilkan digit ...', 'options' => ['1', '2', '3', '4'], 'correct' => 1, 'explanation' => '8 + 4 = 12, digit satuannya 2.'],
                ]),
            ],
            'angka-hilang-dan-huruf-hilang' => [
                'modules' => [
                    $this->module(
                        'modul-angka-hilang-dasar',
                        'Angka Hilang dan Huruf Hilang: Membaca Pola dengan Tenang',
                        'Pelajari cara menemukan elemen yang hilang dari deret angka dan huruf.',
                        'Modul ini mengajarkan identifikasi pola kenaikan, penurunan, lompatan tetap, dan urutan alfabet sederhana.',
                        'Pisahkan dulu apakah polanya numerik, alfabet, atau selang-seling.',
                        'Jika ada dua kemungkinan pola, uji keduanya ke seluruh deret sebelum memilih jawaban.',
                        ModuleLevelEnum::BASIC,
                        20,
                    ),
                    $this->module(
                        'modul-angka-hilang-review',
                        'Review Pola Deret untuk Akurasi Tinggi',
                        'Perkuat cara memeriksa pola sebelum mengunci jawaban.',
                        'Setelah dasar pola terbentuk, modul ini membantu user lebih cepat memutuskan pola yang benar tanpa tergesa.',
                        'Cek selisih dua langkah sekaligus saat pola satu langkah terasa tidak konsisten.',
                        'Gunakan alfabet sebagai urutan posisi, bukan sekadar nama huruf.',
                        ModuleLevelEnum::INTERMEDIATE,
                        18,
                    ),
                ],
                'questions' => $this->objectiveQuestionSet('MIS', [
                    ['question' => '2, 4, 6, __, 10', 'options' => ['7', '8', '9', '10'], 'correct' => 1, 'explanation' => 'Deret bertambah 2.'],
                    ['question' => 'A, C, E, __, I', 'options' => ['F', 'G', 'H', 'J'], 'correct' => 1, 'explanation' => 'Huruf meloncat satu: A, C, E, G, I.'],
                    ['question' => '10, 20, __, 40, 50', 'options' => ['25', '30', '35', '45'], 'correct' => 1, 'explanation' => 'Deret bertambah 10.'],
                    ['question' => 'B, D, F, __, J', 'options' => ['G', 'H', 'I', 'K'], 'correct' => 1, 'explanation' => 'Huruf meloncat satu: B, D, F, H, J.'],
                    ['question' => '3, 6, 9, __, 15', 'options' => ['10', '11', '12', '13'], 'correct' => 2, 'explanation' => 'Deret bertambah 3.'],
                    ['question' => 'Z, X, V, __, R', 'options' => ['S', 'T', 'U', 'W'], 'correct' => 1, 'explanation' => 'Urutan huruf mundur selang dua: Z, X, V, T, R.'],
                    ['question' => '5, 10, 15, __, 25', 'options' => ['18', '19', '20', '21'], 'correct' => 2, 'explanation' => 'Deret bertambah 5.'],
                    ['question' => 'M, O, Q, __, U', 'options' => ['R', 'S', 'T', 'V'], 'correct' => 1, 'explanation' => 'Huruf meloncat satu: M, O, Q, S, U.'],
                    ['question' => '12, 15, 18, __, 24', 'options' => ['19', '20', '21', '22'], 'correct' => 2, 'explanation' => 'Deret bertambah 3.'],
                    ['question' => 'C, F, I, __, O', 'options' => ['K', 'L', 'M', 'N'], 'correct' => 1, 'explanation' => 'Posisi huruf bertambah 3: C, F, I, L, O.'],
                    ['question' => '30, 27, 24, __, 18', 'options' => ['20', '21', '22', '23'], 'correct' => 1, 'explanation' => 'Deret berkurang 3.'],
                    ['question' => 'H, J, L, __, P', 'options' => ['M', 'N', 'O', 'Q'], 'correct' => 1, 'explanation' => 'Huruf meloncat satu: H, J, L, N, P.'],
                ]),
            ],
        ];
    }

    protected function module(
        string $slug,
        string $title,
        string $summary,
        string $content,
        string $tips,
        string $tricks,
        ModuleLevelEnum $level,
        int $estimatedMinutes,
    ): array {
        return [
            'slug' => $slug,
            'title' => $title,
            'summary' => $summary,
            'content' => $this->sectionedContent($content),
            'tips' => $this->sectionBlock('Tips', $tips),
            'tricks' => $this->sectionBlock('Trik', $tricks),
            'level' => $level,
            'estimated_minutes' => $estimatedMinutes,
        ];
    }

    protected function objectiveQuestionSet(string $prefix, array $questions): array
    {
        return collect($questions)
            ->map(function (array $question, int $index) use ($prefix): array {
                $difficulty = match (true) {
                    $index < 4 => DifficultyEnum::EASY,
                    $index < 8 => DifficultyEnum::MEDIUM,
                    default => DifficultyEnum::HARD,
                };

                return [
                    'code' => $prefix.'-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'question_type' => QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE,
                    'difficulty' => $difficulty,
                    'question_text' => $question['question'],
                    'explanation_text' => $question['explanation'],
                    'answer_key_text' => $question['options'][$question['correct']],
                    'options' => collect($question['options'])
                        ->map(fn (string $optionText, int $optionIndex): array => [
                            'option_key' => chr(65 + $optionIndex),
                            'option_text' => $optionText,
                            'is_correct' => $optionIndex === $question['correct'],
                        ])->all(),
                ];
            })
            ->all();
    }

    protected function sectionedContent(string $intro): string
    {
        return implode("\n\n", [
            $this->sectionBlock('Pengenalan', $intro),
            $this->sectionBlock('Tujuan', 'Tujuan modul ini adalah membuat user memahami pola dasar subtes, tahu apa yang harus diamati, dan punya kerangka kerja sebelum menjawab soal.'),
            $this->sectionBlock('Cara Mengerjakan', 'Baca stem soal dengan pelan, identifikasi pola utama, eliminasi opsi yang jelas salah, lalu kunci jawaban setelah relasi atau operasi benar-benar teruji.'),
            $this->sectionBlock('Contoh', 'Gunakan contoh-contoh di bawah ini sebagai latihan membaca pola. Jangan hanya menghafal jawaban; fokuslah pada alasan kenapa opsi yang benar paling konsisten.'),
        ]);
    }

    protected function sectionBlock(string $title, string $body): string
    {
        return "## {$title}\n\n{$body}";
    }
}
