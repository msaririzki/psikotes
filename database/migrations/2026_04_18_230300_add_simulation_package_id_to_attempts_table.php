<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->foreignId('simulation_package_id')
                ->nullable()
                ->after('learning_module_id')
                ->constrained('simulation_packages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('simulation_package_id');
        });
    }
};
