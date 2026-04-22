<?php

namespace Database\Seeders;

use App\Enums\QuestionStatusEnum;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Subtest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use RuntimeException;

class PolriObjectiveQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $payload = $this->loadPayload();
        $authorId = User::query()
            ->where('email', 'admin@prikotes.test')
            ->orWhere('email', 'superadmin@prikotes.test')
            ->value('id');

        foreach ($payload['questions'] as $questionData) {
            $subtest = Subtest::query()
                ->where('slug', $questionData['subtest_slug'])
                ->first();

            if (! $subtest) {
                continue;
            }

            $question = Question::query()->updateOrCreate(
                ['code' => $questionData['code']],
                [
                    'category_id' => $subtest->category_id,
                    'subtest_id' => $subtest->id,
                    'question_type' => $questionData['question_type'],
                    'difficulty' => $questionData['difficulty'],
                    'question_text' => $questionData['question_text'],
                    'question_image' => $questionData['question_image'],
                    'explanation_text' => $questionData['explanation_text'],
                    'answer_key_text' => $questionData['answer_key_text'],
                    'status' => QuestionStatusEnum::PUBLISHED,
                    'source_reference' => $questionData['source_reference'],
                    'created_by' => $authorId,
                    'updated_by' => $authorId,
                ],
            );

            foreach ($questionData['options'] as $optionIndex => $optionData) {
                QuestionOption::query()->updateOrCreate(
                    [
                        'question_id' => $question->id,
                        'option_key' => $optionData['option_key'],
                    ],
                    [
                        'option_text' => $optionData['option_text'],
                        'option_image' => $optionData['option_image'] ?? null,
                        'is_correct' => $optionData['is_correct'],
                        'sort_order' => $optionIndex + 1,
                    ],
                );
            }
        }
    }

    protected function loadPayload(): array
    {
        $path = database_path('data/polri_2025_objective_questions.json');

        if (! File::exists($path)) {
            throw new RuntimeException("Payload bank soal tidak ditemukan di {$path}.");
        }

        $decoded = json_decode(File::get($path), true);

        if (! is_array($decoded) || ! isset($decoded['questions']) || ! is_array($decoded['questions'])) {
            throw new RuntimeException('Payload bank soal POLRI 2025 tidak valid.');
        }

        return $decoded;
    }
}
