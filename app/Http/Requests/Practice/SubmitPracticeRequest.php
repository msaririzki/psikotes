<?php

namespace App\Http\Requests\Practice;

use App\Models\Attempt;
use Illuminate\Foundation\Http\FormRequest;

class SubmitPracticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Attempt $attempt */
        $attempt = $this->route('attempt');

        return $this->user()->can('submitPractice', $attempt);
    }

    public function rules(): array
    {
        return [
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'integer', 'exists:question_options,id'],
        ];
    }

    public function payload(): array
    {
        return collect($this->validated('answers', []))
            ->mapWithKeys(
                fn ($value, $key): array => [(int) $key => $value ? (int) $value : null],
            )
            ->all();
    }
}
