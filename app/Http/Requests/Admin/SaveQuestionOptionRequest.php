<?php

namespace App\Http\Requests\Admin;

use App\Models\QuestionOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveQuestionOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $option = $this->route('option');

        return $option
            ? $this->user()->can('update', $option)
            : $this->user()->can('create', QuestionOption::class);
    }

    public function rules(): array
    {
        $question = $this->route('question');
        $option = $this->route('option');

        return [
            'option_key' => [
                'required',
                'string',
                'max:10',
                Rule::unique('question_options', 'option_key')
                    ->where(fn ($query) => $query->where('question_id', $question->id))
                    ->ignore($option),
            ],
            'option_text' => ['nullable', 'string'],
            'option_image' => ['nullable', 'string', 'max:2048'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'is_correct' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'option_key' => strtoupper(trim((string) $this->string('option_key'))),
            'sort_order' => $this->integer('sort_order', 0),
            'weight' => $this->filled('weight') ? $this->input('weight') : null,
            'is_correct' => $this->has('is_correct') ? $this->boolean('is_correct') : null,
        ]);
    }

    public function payload(): array
    {
        return [
            ...$this->validated(),
            'sort_order' => $this->validated()['sort_order'] ?? 0,
        ];
    }
}
