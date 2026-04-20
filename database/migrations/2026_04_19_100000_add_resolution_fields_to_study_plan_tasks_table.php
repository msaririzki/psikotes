<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_plan_tasks', function (Blueprint $table): void {
            $table->string('completion_source')->nullable()->after('completed_at');
            $table->string('resolved_activity_type')->nullable()->after('completion_source');
            $table->unsignedBigInteger('resolved_activity_id')->nullable()->after('resolved_activity_type');
        });
    }

    public function down(): void
    {
        Schema::table('study_plan_tasks', function (Blueprint $table): void {
            $table->dropColumn([
                'completion_source',
                'resolved_activity_type',
                'resolved_activity_id',
            ]);
        });
    }
};
