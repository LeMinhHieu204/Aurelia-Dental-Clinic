<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('patients.view');
    }

    public function view(User $user, Patient $patient): bool
    {
        return $user->hasPermission('patients.view');
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->hasPermission('patients.view');
    }
}
