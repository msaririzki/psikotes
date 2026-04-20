<?php

namespace App\Http\Requests\Admin;

use App\Enums\DifficultyEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $question = $this->route('question');

        return $question
            ? $this->user()->can('update', $question)
            : $this->user()->can('create', Question::class);
    }

    public function rules(): array
    {
        $question = $this->route('question');

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'subtest_id' => [
                'required',
                'integer',
                Rule::exists('subtests', 'id')->where(fn ($query) => $query->where('category_id', $this->input('category_id'))),
            ],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('questions', 'code')->ignore($question)],
            'question_type' => ['required', Rule::enum(QuestionTypeEnum::class)],
            'difficulty' => ['required', Rule::enum(DifficultyEnum::class)],
            'question_text' => ['required', 'string'],
            'question_image' => ['nullable', 'string', 'max:2048'],
            'extra_data' => ['nullable', 'json'],
            'explanation_text' => ['nullable', 'string'],
            'answer_key_text' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(QuestionStatusEnum::class)],
            'source_reference' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => $this->filled('code') ? strtoupper((string) $this->string('code')) : null,
            'extra_data' => $this->filled('extra_data') ? trim((string) $this->string('extra_data')) : null,
        ]);
    }

    public function payload(): array
    {
        $validated = $this->validated();

        return [
            ...$validated,
            'extra_data' => filled($validated['extra_data'] ?? null)
                ? json_decode((string) $validated['extra_data'], true, flags: JSON_THROW_ON_ERROR)
                : null,
        ];
    }
}
