<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Support\Collection;

class AttemptSnapshotService
{
    public function attemptQuestionPayload(
        Question $question,
        int $displayOrder,
        ?string $sectionName = null,
    ): array {
        return [
            'question_id' => $question->id,
            'display_order' => $displayOrder,
            'section_name' => $sectionName,
            'snapshot' => $this->questionSnapshot($question),
        ];
    }

    public function questionSnapshot(Question $question): array
    {
        $options = $question->relationLoaded('options')
            ? $question->options
            : $question->options()->orderBy('sort_order')->orderBy('option_key')->get();

        return [
            'question' => [
                'id' => $question->id,
                'category_id' => $question->category_id,
                'subtest_id' => $question->subtest_id,
                'code' => $question->code,
                'question_type' => $question->question_type?->value,
                'question_type_label' => $question->question_type?->label(),
                'difficulty' => $question->difficulty?->value,
                'difficulty_label' => $question->difficulty?->label(),
                'question_text' => $question->question_text,
                'question_image' => $question->question_image,
                'explanation_text' => $question->explanation_text,
                'answer_key_text' => $question->answer_key_text,
            ],
            'options' => $options->map(
                fn (QuestionOption $option): array => $this->selectedOptionSnapshot($option),
            )->values()->all(),
        ];
    }

    public function selectedOptionSnapshot(QuestionOption $option): array
    {
        return [
            'id' => $option->id,
            'option_key' => $option->option_key,
            'option_text' => $option->option_text,
            'option_image' => $option->option_image,
            'is_correct' => (bool) $option->is_correct,
            'sort_order' => $option->sort_order,
        ];
    }

    public function optionSnapshotById(array $snapshot, ?int $optionId): ?array
    {
        if ($optionId === null) {
            return null;
        }

        return collect(data_get($snapshot, 'options', []))
            ->firstWhere('id', $optionId);
    }

    public function correctOptionSnapshot(array $snapshot): ?array
    {
        return collect(data_get($snapshot, 'options', []))
            ->first(fn (array $option): bool => (bool) ($option['is_correct'] ?? false));
    }

    public function selectedOptionFromAnswer(array $answerJson): ?array
    {
        return data_get($answerJson, 'selected_option');
    }
}
