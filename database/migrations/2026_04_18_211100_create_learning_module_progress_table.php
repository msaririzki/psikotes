<?php

use App\Enums\LearningModuleProgressStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_module_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table
                ->foreignId('learning_module_id')
                ->constrained('learning_modules')
                ->cascadeOnDelete();
            $table
                ->string('status')
                ->default(LearningModuleProgressStatusEnum::IN_PROGRESS->value);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_viewed_at')->nullable();
            $table
                ->foreignId('last_quiz_attempt_id')
                ->nullable()
                ->constrained('attempts')
                ->nullOnDelete();
            $table->decimal('last_quiz_score', 8, 2)->nullable();
            $table->unsignedSmallInteger('quiz_attempts_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'learning_module_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_module_progress');
    }
};
