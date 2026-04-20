<?php

use App\Enums\DifficultyEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subtest_id')->constrained()->cascadeOnDelete();
            $table->string('code')->nullable()->unique();
            $table->string('question_type')->default(QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE->value);
            $table->string('difficulty')->default(DifficultyEnum::EASY->value);
            $table->longText('question_text');
            $table->string('question_image')->nullable();
            $table->json('extra_data')->nullable();
            $table->longText('explanation_text')->nullable();
            $table->text('answer_key_text')->nullable();
            $table->string('status')->default(QuestionStatusEnum::DRAFT->value);
            $table->string('source_reference')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['subtest_id', 'status']);
            $table->index(['question_type', 'difficulty']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
