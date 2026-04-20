<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class SaveCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = $this->route('category');

        return $category
            ? $this->user()->can('update', $category)
            : $this->user()->can('create', Category::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->filled('slug') ? Str::slug((string) $this->string('slug')) : null,
            'sort_order' => $this->integer('sort_order', 0),
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
