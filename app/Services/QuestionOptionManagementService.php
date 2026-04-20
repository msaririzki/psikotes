<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuestionOptionManagementService
{
    public function create(Question $question, array $payload, User $actor): QuestionOption
    {
        return DB::transaction(function () use ($question, $payload): QuestionOption {
            return $question->options()->create($payload);
        });
    }

    public function update(QuestionOption $option, array $payload, User $actor): QuestionOption
    {
        return DB::transaction(function () use ($option, $payload): QuestionOption {
            $option->fill($payload)->save();

            return $option->refresh();
        });
    }

    public function delete(QuestionOption $option): void
    {
        if ($option->selectedInAnswers()->exists()) {
            throw ValidationException::withMessages([
                'option' => 'Opsi tidak bisa dihapus karena sudah dipilih pada histori jawaban.',
            ]);
        }

        $option->delete();
    }
}
