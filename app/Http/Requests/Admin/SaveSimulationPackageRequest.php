<?php

namespace App\Http\Requests\Admin;

use App\Models\SimulationPackage;
use App\Models\Subtest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class SaveSimulationPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $simulationPackage = $this->route('simulationPackage');

        return $simulationPackage
            ? $this->user()->can('update', $simulationPackage)
            : $this->user()->can('create', SimulationPackage::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description' => ['nullable', 'string'],
            'instruction' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:10', 'max:300'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_published' => ['nullable', 'boolean'],
            'subtests' => ['required', 'array', 'min:1'],
            'subtests.*.subtest_id' => ['required', 'integer', 'exists:subtests,id', 'distinct'],
            'subtests.*.question_count' => ['required', 'integer', 'min:1', 'max:200'],
            'subtests.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $subtestIds = collect($this->input('subtests', []))
                    ->pluck('subtest_id')
                    ->filter()
                    ->map(fn ($id): int => (int) $id)
                    ->values();

                if ($subtestIds->isEmpty()) {
                    return;
                }

                $activeSubtestIds = Subtest::query()
                    ->whereIn('id', $subtestIds)
                    ->where('is_active', true)
                    ->pluck('id');

                $invalidSubtestIds = $subtestIds->diff($activeSubtestIds);

                if ($invalidSubtestIds->isNotEmpty()) {
                    $validator->errors()->add(
                        'subtests',
                        'Pilih subtes yang aktif dan valid untuk paket simulasi.',
                    );
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $subtests = collect($this->input('subtests', []))
            ->filter(fn ($row) => filled(data_get($row, 'subtest_id')))
            ->values()
            ->map(fn ($row, $index): array => [
                'subtest_id' => (int) data_get($row, 'subtest_id'),
                'question_count' => (int) data_get($row, 'question_count', 0),
                'sort_order' => (int) data_get($row, 'sort_order', $index),
            ])
            ->all();

        $this->merge([
            'slug' => $this->filled('slug') ? Str::slug((string) $this->string('slug')) : null,
            'duration_minutes' => $this->integer('duration_minutes'),
            'sort_order' => $this->filled('sort_order') ? $this->integer('sort_order') : 0,
            'is_published' => $this->boolean('is_published'),
            'subtests' => $subtests,
        ]);
    }

    public function payload(): array
    {
        $validated = $this->validated();

        return [
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? null,
            'description' => $validated['description'] ?? null,
            'instruction' => $validated['instruction'] ?? null,
            'duration_minutes' => (int) $validated['duration_minutes'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_published' => $validated['is_published'] ?? false,
            'subtests' => collect($validated['subtests'])
                ->map(fn (array $row): array => [
                    'subtest_id' => (int) $row['subtest_id'],
                    'question_count' => (int) $row['question_count'],
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                ])
                ->values()
                ->all(),
        ];
    }
}
