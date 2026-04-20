<?php

namespace App\Http\Requests\Simulation;

use App\Models\Attempt;
use Illuminate\Foundation\Http\FormRequest;

class SubmitSimulationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Attempt $attempt */
        $attempt = $this->route('attempt');

        return $this->user()->can('submitSimulation', $attempt);
    }

    public function rules(): array
    {
        return [
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'integer', 'exists:question_options,id'],
            'flags' => ['nullable', 'array'],
            'flags.*' => ['boolean'],
        ];
    }

    public function payload(): array
    {
        return [
            'answers' => collect($this->validated('answers', []))
                ->mapWithKeys(fn ($value, $key): array => [(int) $key => $value ? (int) $value : null])
                ->all(),
            'flags' => collect($this->validated('flags', []))
                ->mapWithKeys(fn ($value, $key): array => [(int) $key => (bool) $value])
                ->all(),
        ];
    }
}
