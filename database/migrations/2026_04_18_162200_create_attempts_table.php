<?php

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('mode')->default(AttemptModeEnum::PRACTICE->value);
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subtest_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default(AttemptStatusEnum::DRAFT->value);
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedSmallInteger('total_questions')->default(0);
            $table->unsignedSmallInteger('answered_questions')->default(0);
            $table->unsignedSmallInteger('correct_answers')->default(0);
            $table->unsignedSmallInteger('wrong_answers')->default(0);
            $table->unsignedSmallInteger('blank_answers')->default(0);
            $table->decimal('score_total', 8, 2)->nullable();
            $table->decimal('accuracy', 5, 2)->nullable();
            $table->json('result_summary')->nullable();
            $table->longText('analysis_text')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'mode', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
