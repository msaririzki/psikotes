<?php

namespace App\Http\Requests\Admin;

use App\Enums\ScoringTypeEnum;
use App\Models\Subtest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveSubtestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $subtest = $this->route('subtest');

        return $subtest
            ? $this->user()->can('update', $subtest)
            : $this->user()->can('create', Subtest::class);
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description' => ['nullable', 'string'],
            'instruction' => ['nullable', 'string'],
            'scoring_type' => ['required', Rule::enum(ScoringTypeEnum::class)],
            'default_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:300'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->filled('slug') ? Str::slug((string) $this->string('slug')) : null,
            'sort_order' => $this->integer('sort_order', 0),
            'default_duration_minutes' => $this->filled('default_duration_minutes')
                ? $this->integer('default_duration_minutes')
                : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function payload(): array
    {
        return [
            ...$this->validated(),
            'sort_order' => $this->validated()['sort_order'] ?? 0,
            'is_active' => $this->validated()['is_active'] ?? false,
        ];
    }
}
