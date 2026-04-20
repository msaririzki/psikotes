<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DifficultyEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveQuestionRequest;
use App\Models\Category;
use App\Models\Question;
use App\Models\Subtest;
use App\Services\QuestionManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class QuestionController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Question::class);

        $filters = [
            'q' => trim((string) $request->string('q')),
            'category_id' => $request->integer('category_id') ?: null,
            'subtest_id' => $request->integer('subtest_id') ?: null,
            'question_type' => (string) $request->string('question_type', 'all'),
            'difficulty' => (string) $request->string('difficulty', 'all'),
            'status' => (string) $request->string('status', 'all'),
        ];

        $questions = Question::query()
            ->with(['category:id,name', 'subtest:id,name'])
            ->withCount('options')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($innerQuery) use ($filters) {
                    $innerQuery
                        ->where('code', 'like', '%'.$filters['q'].'%')
                        ->orWhere('question_text', 'like', '%'.$filters['q'].'%')
                        ->orWhere('source_reference', 'like', '%'.$filters['q'].'%');
                });
            })
            ->when($filters['category_id'], fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['subtest_id'], fn ($query, $subtestId) => $query->where('subtest_id', $subtestId))
            ->when($filters['question_type'] !== 'all', fn ($query) => $query->where('question_type', $filters['question_type']))
            ->when($filters['difficulty'] !== 'all', fn ($query) => $query->where('difficulty', $filters['difficulty']))
            ->when($filters['status'] !== 'all', fn ($query) => $query->where('status', $filters['status']))
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Question $question): array => [
                'id' => $question->id,
                'code' => $question->code,
                'question_text' => Str::limit(trim(strip_tags((string) $question->question_text)), 100),
                'question_type' => $question->question_type?->value,
                'question_type_label' => $question->question_type?->label(),
                'difficulty' => $question->difficulty?->value,
                'difficulty_label' => $question->difficulty?->label(),
                'status' => $question->status?->value,
                'status_label' => $question->status?->label(),
                'category' => $question->category?->name,
                'subtest' => $question->subtest?->name,
                'options_count' => $question->options_count,
                'updated_at' => $question->updated_at?->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Questions/Index', [
            'questions' => $questions,
            'filters' => $filters,
            'stats' => [
                'total' => Question::query()->count(),
                'published' => Question::query()->where('status', QuestionStatusEnum::PUBLISHED)->count(),
                'draft' => Question::query()->where('status', QuestionStatusEnum::DRAFT)->count(),
                'review' => Question::query()->where('status', QuestionStatusEnum::REVIEW)->count(),
            ],
            'categories' => $this->categories(),
            'subtests' => $this->subtests(),
            'questionTypes' => $this->questionTypes(),
            'difficulties' => $this->difficulties(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Question::class);

        return Inertia::render('Admin/Questions/Create', [
            'question' => null,
            'categories' => $this->categories(),
            'subtests' => $this->subtests(),
            'questionTypes' => $this->questionTypes(),
            'difficulties' => $this->difficulties(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function store(
        SaveQuestionRequest $request,
        QuestionManagementService $service,
    ): RedirectResponse {
        $question = $service->create($request->payload(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Soal berhasil dibuat.',
        ]);

        return to_route('admin.questions.edit', $question);
    }

    public function edit(Question $question): Response
    {
        $this->authorize('update', $question);

        return Inertia::render('Admin/Questions/Edit', [
            'question' => [
                'id' => $question->id,
                'category_id' => $question->category_id,
                'subtest_id' => $question->subtest_id,
                'code' => $question->code,
                'question_type' => $question->question_type?->value,
                'difficulty' => $question->difficulty?->value,
                'question_text' => $question->question_text,
                'question_image' => $question->question_image,
                'extra_data' => $question->extra_data ? json_encode($question->extra_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '',
                'explanation_text' => $question->explanation_text,
                'answer_key_text' => $question->answer_key_text,
                'status' => $question->status?->value,
                'source_reference' => $question->source_reference,
                'options_count' => $question->options()->count(),
            ],
            'categories' => $this->categories(),
            'subtests' => $this->subtests(),
            'questionTypes' => $this->questionTypes(),
            'difficulties' => $this->difficulties(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(
        SaveQuestionRequest $request,
        Question $question,
        QuestionManagementService $service,
    ): RedirectResponse {
        $service->update($question, $request->payload(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Soal berhasil diperbarui.',
        ]);

        return to_route('admin.questions.edit', $question);
    }

    public function destroy(
        Question $question,
        QuestionManagementService $service,
    ): RedirectResponse {
        $this->authorize('delete', $question);

        $service->delete($question);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Soal berhasil dihapus.',
        ]);

        return to_route('admin.questions.index');
    }

    public function updateStatus(
        Request $request,
        Question $question,
        QuestionManagementService $service,
    ): RedirectResponse {
        $this->authorize('updateStatus', $question);

        $payload = $request->validate([
            'status' => ['required', Rule::enum(QuestionStatusEnum::class)],
        ]);

        $service->updateStatus(
            $question,
            QuestionStatusEnum::from($payload['status']),
            $request->user(),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Status soal berhasil diperbarui.',
        ]);

        return back();
    }

    private function categories(): array
    {
        return Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->all();
    }

    private function subtests(): array
    {
        return Subtest::query()
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'category_id', 'name'])
            ->map(fn (Subtest $subtest): array => [
                'id' => $subtest->id,
                'category_id' => $subtest->category_id,
                'name' => $subtest->name,
            ])
            ->all();
    }

    private function questionTypes(): array
    {
        return collect(QuestionTypeEnum::cases())
            ->map(fn (QuestionTypeEnum $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ])
            ->all();
    }

    private function difficulties(): array
    {
        return collect(DifficultyEnum::cases())
            ->map(fn (DifficultyEnum $difficulty): array => [
                'value' => $difficulty->value,
                'label' => $difficulty->label(),
            ])
            ->all();
    }

    private function statuses(): array
    {
        return collect(QuestionStatusEnum::cases())
            ->map(fn (QuestionStatusEnum $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
            ])
            ->all();
    }
}
