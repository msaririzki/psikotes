<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_plan_task_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('study_plan_task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_type');
            $table->text('description')->nullable();
            $table->json('event_payload')->nullable();
            $table->timestamp('happened_at');

            $table->index(['study_plan_task_id', 'happened_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_plan_task_events');
    }
};
