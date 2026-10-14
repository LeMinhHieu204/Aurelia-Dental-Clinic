<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['name', 'slug', 'address', 'country', 'city', 'phone', 'email', 'timezone', 'opening_hours', 'closure_days', 'map_url', 'is_active', 'public_visibility', 'is_demo'];
    protected $casts = ['opening_hours' => 'array', 'closure_days' => 'array', 'is_active' => 'boolean', 'public_visibility' => 'boolean', 'is_demo' => 'boolean'];
    public function doctors() { return $this->belongsToMany(Doctor::class); }
    public function treatments() { return $this->belongsToMany(Treatment::class); }
    public function appointments() { return $this->hasMany(AppointmentRequest::class); }
    public function scopePublicVisible(Builder $query): Builder { return $query->where('is_active', true)->where('public_visibility', true)->when(app()->environment('production'), fn ($q) => $q->where('is_demo', false)); }
}
