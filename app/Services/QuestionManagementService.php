<?php

namespace App\Services;

use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class QuestionManagementService
{
    public function create(array $payload, User $actor): Question
    {
        return DB::transaction(function () use ($payload, $actor): Question {
            $payload['code'] = $payload['code'] ?: $this->generateCode($payload['subtest_id']);
            $payload['created_by'] = $actor->id;
            $payload['updated_by'] = $actor->id;

            $question = Question::query()->create($payload);

            if ($question->status === QuestionStatusEnum::PUBLISHED) {
                $this->guardPublishable($question);
            }

            return $question->refresh();
        });
    }

    public function update(Question $question, array $payload, User $actor): Question
    {
        return DB::transaction(function () use ($question, $payload, $actor): Question {
            $payload['code'] = $payload['code'] ?: $question->code ?: $this->generateCode($payload['subtest_id']);
            $payload['updated_by'] = $actor->id;

            $question->fill($payload)->save();

            if ($question->status === QuestionStatusEnum::PUBLISHED) {
                $this->guardPublishable($question);
            }

            return $question->refresh();
        });
    }

    public function updateStatus(Question $question, QuestionStatusEnum $status, User $actor): Question
    {
        if ($status === QuestionStatusEnum::PUBLISHED) {
            $this->guardPublishable($question);
        }

        $question->forceFill([
            'status' => $status,
            'updated_by' => $actor->id,
        ])->save();

        return $question->refresh();
    }

    public function delete(Question $question): void
    {
        if ($question->attemptQuestions()->exists() || $question->attemptAnswers()->exists()) {
            throw ValidationException::withMessages([
                'question' => 'Soal tidak bisa dihapus karena sudah terhubung dengan histori attempt.',
            ]);
        }

        $question->delete();
    }

    protected function guardPublishable(Question $question): void
    {
        if (! $question->question_text || blank(trim(strip_tags($question->question_text)))) {
            throw ValidationException::withMessages([
                'status' => 'Soal tidak bisa dipublish tanpa isi pertanyaan.',
            ]);
        }

        if (in_array($question->question_type, [
            QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE,
            QuestionTypeEnum::MULTIPLE_CHOICE_IMAGE,
            QuestionTypeEnum::TRUE_FALSE,
        ], true)) {
            $optionsCount = $question->options()->count();
            $correctCount = $question->options()->where('is_correct', true)->count();

            if ($optionsCount < 2 || $correctCount < 1) {
                throw ValidationException::withMessages([
                    'status' => 'Soal pilihan ganda hanya bisa dipublish jika memiliki minimal 2 opsi dan 1 jawaban benar.',
                ]);
            }
        }
    }

    protected function generateCode(int $subtestId): string
    {
        return sprintf('Q-%d-%s', $subtestId, Str::upper(Str::random(6)));
    }
}
