<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'simulation_package_id',
    'subtest_id',
    'question_count',
    'sort_order',
])]
class SimulationPackageSubtest extends Model
{
    public function simulationPackage(): BelongsTo
    {
        return $this->belongsTo(SimulationPackage::class);
    }

    public function subtest(): BelongsTo
    {
        return $this->belongsTo(Subtest::class);
    }
}
