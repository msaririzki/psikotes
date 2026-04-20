<?php

namespace App\Policies;

use App\Models\SimulationPackage;
use App\Models\User;
use App\Policies\Concerns\HandlesAdminCmsAuthorization;

class SimulationPackagePolicy
{
    use HandlesAdminCmsAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->canManageCms($user);
    }

    public function view(User $user, SimulationPackage $simulationPackage): bool
    {
        return $this->canManageCms($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageCms($user);
    }

    public function update(User $user, SimulationPackage $simulationPackage): bool
    {
        return $this->canManageCms($user);
    }

    public function delete(User $user, SimulationPackage $simulationPackage): bool
    {
        return $this->canManageCms($user);
    }

    public function togglePublication(User $user, SimulationPackage $simulationPackage): bool
    {
        return $this->canManageCms($user);
    }

    public function viewCatalog(User $user, SimulationPackage $simulationPackage): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $simulationPackage->is_published
            && $simulationPackage->packageSubtests()->exists();
    }

    public function startSimulation(User $user, SimulationPackage $simulationPackage): bool
    {
        return $this->viewCatalog($user, $simulationPackage);
    }
}
