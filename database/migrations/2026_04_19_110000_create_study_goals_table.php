<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_goals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('goal_key');
            $table->string('period_type', 20);
            $table->string('goal_type', 50);
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('rationale')->nullable();
            $table->date('period_starts_on');
            $table->date('period_ends_on');
            $table->json('focus_payload')->nullable();
            $table->json('target_payload')->nullable();
            $table->json('baseline_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'goal_key']);
            $table->index(['user_id', 'period_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_goals');
    }
};
