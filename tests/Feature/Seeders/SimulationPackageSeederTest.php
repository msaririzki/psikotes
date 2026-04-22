<?php

use App\Models\SimulationPackage;
use Database\Seeders\DomainReferenceSeeder;
use Database\Seeders\PolriObjectiveQuestionSeeder;
use Database\Seeders\SimulationPackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('simulation package seeder builds real packages from imported objective bank', function () {
    $this->seed(DomainReferenceSeeder::class);
    $this->seed(PolriObjectiveQuestionSeeder::class);
    $this->seed(SimulationPackageSeeder::class);

    $fullPackage = SimulationPackage::query()
        ->where('slug', 'simulasi-real-lengkap-polri-2025')
        ->first();

    $academicPackage = SimulationPackage::query()
        ->where('slug', 'simulasi-real-akademik-polri-2025')
        ->first();

    $speedPackage = SimulationPackage::query()
        ->where('slug', 'simulasi-real-kecepatan-polri-2025')
        ->first();

    expect($fullPackage)->not->toBeNull();
    expect($fullPackage->question_count)->toBe(445);
    expect($fullPackage->packageSubtests()->count())->toBe(6);

    expect($academicPackage)->not->toBeNull();
    expect($academicPackage->question_count)->toBe(135);
    expect($academicPackage->packageSubtests()->count())->toBe(4);

    expect($speedPackage)->not->toBeNull();
    expect($speedPackage->question_count)->toBe(50);
    expect($speedPackage->packageSubtests()->count())->toBe(2);
});
