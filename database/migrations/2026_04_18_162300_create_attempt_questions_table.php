<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempt_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('display_order');
            $table->string('section_name')->nullable();
            $table->timestamps();

            $table->unique(['attempt_id', 'question_id']);
            $table->unique(['attempt_id', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempt_questions');
    }
};
