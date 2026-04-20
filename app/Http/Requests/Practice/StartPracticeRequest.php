<?php

namespace App\Http\Requests\Practice;

use App\Enums\PracticeDifficultyFilterEnum;
use App\Models\Subtest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartPracticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Subtest $subtest */
        $subtest = $this->route('subtest');

        return $this->user()->can('startPractice', $subtest);
    }

    public function rules(): array
    {
        return [
            'difficulty' => ['required', Rule::enum(PracticeDifficultyFilterEnum::class)],
            'question_count' => ['required', 'integer', 'min:3', 'max:50'],
            'timer_minutes' => ['nullable', 'integer', 'min:5', 'max:180'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('timer_minutes')) {
            $this->merge(['timer_minutes' => null]);
        }
    }

    public function payload(): array
    {
        $validated = $this->validated();

        return [
            'difficulty' => PracticeDifficultyFilterEnum::from($validated['difficulty']),
            'question_count' => (int) $validated['question_count'],
            'timer_minutes' => isset($validated['timer_minutes'])
                ? (int) $validated['timer_minutes']
                : null,
        ];
    }
}
