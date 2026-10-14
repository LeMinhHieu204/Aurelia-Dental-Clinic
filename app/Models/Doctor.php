<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = ['full_name', 'slug', 'professional_title', 'bio', 'qualifications', 'registration_identifier', 'languages', 'portrait_url', 'is_active', 'is_published', 'is_demo', 'display_order', 'archived_at', 'user_id'];
    protected $casts = ['is_active' => 'boolean', 'is_published' => 'boolean', 'is_demo' => 'boolean', 'archived_at' => 'datetime'];
    public function specialties() { return $this->belongsToMany(DoctorSpecialty::class, 'doctor_specialty'); }
    public function treatments() { return $this->belongsToMany(Treatment::class); }
    public function locations() { return $this->belongsToMany(Location::class); }
    public function appointments() { return $this->hasMany(AppointmentRequest::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function availability() { return $this->hasMany(DoctorAvailability::class); }
    public function scopePublicVisible(Builder $query): Builder { return $query->where('is_active', true)->whereNull('archived_at')->where('is_published', true)->when(app()->environment('production'), fn ($q) => $q->where('is_demo', false)); }
    public function isPubliclyVisible(): bool { return $this->is_active && ! $this->archived_at && $this->is_published && (! app()->environment('production') || ! $this->is_demo); }
}
