<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_plan_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('task_key');
            $table->string('status')->default('pending');
            $table->string('type');
            $table->string('track');
            $table->string('source');
            $table->string('title');
            $table->text('description');
            $table->text('reason');
            $table->string('action_label');
            $table->string('action_href');
            $table->unsignedSmallInteger('priority_score')->default(0);
            $table->string('priority_label')->nullable();
            $table->date('recommended_for_date')->nullable();
            $table->date('scheduled_for_date')->nullable();
            $table->date('snoozed_until')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_generated_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'task_key']);
            $table->index(['user_id', 'is_active', 'status']);
            $table->index(['user_id', 'scheduled_for_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_plan_tasks');
    }
};
