<?php

namespace App\Policies;

use App\Models\AppointmentRequest;
use App\Models\User;

class AppointmentRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('appointments.manage') || $user->hasPermission('calendar.own');
    }

    public function view(User $user, AppointmentRequest $appointment): bool
    {
        return $user->hasPermission('appointments.manage') || ($user->hasPermission('calendar.own') && $user->doctor?->is($appointment->doctor));
    }

    public function update(User $user, AppointmentRequest $appointment): bool
    {
        return $user->hasPermission('appointments.manage');
    }
}
