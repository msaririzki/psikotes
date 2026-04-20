<?php

namespace App\Http\Requests\Admin;

use App\Enums\ModuleLevelEnum;
use App\Models\LearningModule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveLearningModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $learningModule = $this->route('learningModule');

        return $learningModule
            ? $this->user()->can('update', $learningModule)
            : $this->user()->can('create', LearningModule::class);
    }

    public function rules(): array
    {
        return [
            'subtest_id' => ['required', 'integer', 'exists:subtests,id'],
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'summary' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'tips' => ['nullable', 'string'],
            'tricks' => ['nullable', 'string'],
            'level' => ['required', Rule::enum(ModuleLevelEnum::class)],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'is_published' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->filled('slug') ? Str::slug((string) $this->string('slug')) : null,
            'estimated_minutes' => $this->filled('estimated_minutes')
                ? $this->integer('estimated_minutes')
                : null,
            'is_published' => $this->boolean('is_published'),
        ]);
    }

    public function payload(): array
    {
        return [
            ...$this->validated(),
            'is_published' => $this->validated()['is_published'] ?? false,
        ];
    }
}
