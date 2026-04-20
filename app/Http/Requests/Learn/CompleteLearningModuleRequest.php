<?php

namespace App\Http\Requests\Learn;

use App\Models\LearningModule;
use Illuminate\Foundation\Http\FormRequest;

class CompleteLearningModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var LearningModule $learningModule */
        $learningModule = $this->route('learningModule');

        return $this->user()->can('markComplete', $learningModule);
    }

    public function rules(): array
    {
        return [];
    }
}
