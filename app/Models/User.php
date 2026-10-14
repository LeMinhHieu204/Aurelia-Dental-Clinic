<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password', 'role_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function role() { return $this->belongsTo(Role::class); }
    public function doctor() { return $this->hasOne(Doctor::class); }

    public function roleName(): ?string { return $this->role?->name ? Str::lower($this->role->name) : null; }

    public function isAdmin(): bool { return in_array($this->roleName(), ['super_admin', 'admin', 'staff', 'receptionist', 'doctor'], true); }

    public function hasPermission(string $permission): bool
    {
        $permissions = [
            'super_admin' => ['*'],
            'admin' => ['appointments.manage', 'calendar.manage', 'doctors.manage', 'treatments.manage', 'locations.manage', 'patients.view', 'inquiries.manage', 'reviews.manage', 'media.manage', 'content.manage', 'settings.manage', 'audit.view'],
            'staff' => ['appointments.manage', 'calendar.manage', 'patients.view', 'inquiries.manage', 'reviews.manage'],
            'receptionist' => ['appointments.manage', 'calendar.manage', 'patients.view', 'inquiries.manage'],
            'doctor' => ['calendar.own'],
        ];
        $granted = $permissions[$this->roleName() ?? ''] ?? [];

        return in_array('*', $granted, true) || in_array($permission, $granted, true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
