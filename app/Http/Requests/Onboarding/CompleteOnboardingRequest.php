<?php

namespace App\Http\Requests\Onboarding;

use App\Enums\LearningLevelEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'education_level' => ['nullable', 'string', 'max:120'],
            'target_exam' => ['required', 'string', 'max:120'],
            'learning_level' => ['required', Rule::enum(LearningLevelEnum::class)],
            'target_daily_minutes' => ['required', 'integer', 'min:15', 'max:240'],
            'preferred_focus' => ['required', Rule::in(['balanced', 'learn', 'practice', 'simulation'])],
        ];
    }
}
