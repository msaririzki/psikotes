<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveQuestionOptionRequest;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Services\QuestionOptionManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class QuestionOptionController extends Controller
{
    public function index(Question $question, Request $request): Response
    {
        $this->authorize('view', $question);
        $this->authorize('viewAny', QuestionOption::class);

        $filters = [
            'q' => trim((string) $request->string('q')),
            'correctness' => (string) $request->string('correctness', 'all'),
        ];

        $options = $question->options()
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($innerQuery) use ($filters) {
                    $innerQuery
                        ->where('option_key', 'like', '%'.$filters['q'].'%')
                        ->orWhere('option_text', 'like', '%'.$filters['q'].'%');
                });
            })
            ->when($filters['correctness'] === 'correct', fn ($query) => $query->where('is_correct', true))
            ->when($filters['correctness'] === 'incorrect', fn ($query) => $query->where('is_correct', false))
            ->orderBy('sort_order')
            ->orderBy('option_key')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (QuestionOption $option): array => [
                'id' => $option->id,
                'option_key' => $option->option_key,
                'option_text' => Str::limit((string) $option->option_text, 120),
                'option_image' => $option->option_image,
                'weight' => $option->weight,
                'is_correct' => $option->is_correct,
                'sort_order' => $option->sort_order,
                'updated_at' => $option->updated_at?->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Questions/Options/Index', [
            'question' => $this->questionPayload($question),
            'options' => $options,
            'filters' => $filters,
            'stats' => [
                'total' => $question->options()->count(),
                'correct' => $question->options()->where('is_correct', true)->count(),
                'weighted' => $question->options()->whereNotNull('weight')->count(),
            ],
        ]);
    }

    public function create(Question $question): Response
    {
        $this->authorize('view', $question);
        $this->authorize('create', QuestionOption::class);

        return Inertia::render('Admin/Questions/Options/Create', [
            'question' => $this->questionPayload($question),
            'option' => null,
        ]);
    }

    public function store(
        Question $question,
        SaveQuestionOptionRequest $request,
        QuestionOptionManagementService $service,
    ): RedirectResponse {
        $this->authorize('view', $question);

        $service->create($question, $request->payload(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Opsi jawaban berhasil dibuat.',
        ]);

        return to_route('admin.questions.options.index', $question);
    }

    public function edit(Question $question, QuestionOption $option): Response
    {
        $this->authorize('view', $question);
        $this->authorize('update', $option);

        return Inertia::render('Admin/Questions/Options/Edit', [
            'question' => $this->questionPayload($question),
            'option' => [
                'id' => $option->id,
                'option_key' => $option->option_key,
                'option_text' => $option->option_text,
                'option_image' => $option->option_image,
                'weight' => $option->weight,
                'is_correct' => $option->is_correct,
                'sort_order' => $option->sort_order,
            ],
        ]);
    }

    public function update(
        Question $question,
        QuestionOption $option,
        SaveQuestionOptionRequest $request,
        QuestionOptionManagementService $service,
    ): RedirectResponse {
        $this->authorize('view', $question);
        $this->authorize('update', $option);

        $service->update($option, $request->payload(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Opsi jawaban berhasil diperbarui.',
        ]);

        return to_route('admin.questions.options.index', $question);
    }

    public function destroy(
        Question $question,
        QuestionOption $option,
        QuestionOptionManagementService $service,
    ): RedirectResponse {
        $this->authorize('view', $question);
        $this->authorize('delete', $option);

        $service->delete($option);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Opsi jawaban berhasil dihapus.',
        ]);

        return to_route('admin.questions.options.index', $question);
    }

    private function questionPayload(Question $question): array
    {
        $question->load(['category:id,name', 'subtest:id,name']);

        return [
            'id' => $question->id,
            'code' => $question->code,
            'question_text' => Str::limit(trim(strip_tags((string) $question->question_text)), 180),
            'question_type' => $question->question_type?->value,
            'question_type_label' => $question->question_type?->label(),
            'status' => $question->status?->value,
            'status_label' => $question->status?->label(),
            'category' => $question->category?->name,
            'subtest' => $question->subtest?->name,
        ];
    }
}
