<?php

namespace App\Http\Requests\Simulation;

use App\Models\SimulationPackage;
use Illuminate\Foundation\Http\FormRequest;

class StartSimulationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var SimulationPackage $simulationPackage */
        $simulationPackage = $this->route('simulationPackage');

        return $this->user()->can('startSimulation', $simulationPackage);
    }

    public function rules(): array
    {
        return [];
    }
}
