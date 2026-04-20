<?php

namespace App\Http\Requests\StudyPlan;

use App\Models\StudyPlanTask;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudyPlanTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var StudyPlanTask $studyPlanTask */
        $studyPlanTask = $this->route('studyPlanTask');

        return $this->user()->can('update', $studyPlanTask);
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['done', 'snooze', 'reschedule'])],
            'scheduled_for' => [
                Rule::requiredIf(fn (): bool => in_array($this->input('action'), ['snooze', 'reschedule'], true)),
                'nullable',
                'date',
                'after_or_equal:today',
            ],
            'redirect_to' => ['nullable', 'string', 'max:255', 'starts_with:/'],
        ];
    }

    public function payload(): array
    {
        return [
            'action' => $this->string('action')->toString(),
            'scheduled_for' => $this->input('scheduled_for'),
            'redirect_to' => $this->input('redirect_to', '/study-plan'),
        ];
    }
}
