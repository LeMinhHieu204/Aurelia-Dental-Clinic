<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    protected $fillable = ['name', 'slug', 'short_description', 'overview', 'cover_image_url', 'process', 'benefits', 'risks_limitations', 'aftercare', 'faqs', 'duration_label', 'duration_minutes', 'starting_price', 'price_currency', 'consultation_required', 'is_published', 'is_demo', 'display_order', 'seo_title', 'seo_description', 'requires_approval', 'archived_at'];
    protected $casts = ['faqs' => 'array', 'consultation_required' => 'boolean', 'is_published' => 'boolean', 'is_demo' => 'boolean', 'requires_approval' => 'boolean', 'archived_at' => 'datetime'];
    public function doctors() { return $this->belongsToMany(Doctor::class); }
    public function locations() { return $this->belongsToMany(Location::class); }
    public function appointments() { return $this->hasMany(AppointmentRequest::class); }
    public function scopePublicVisible(Builder $query): Builder { return $query->where('is_published', true)->whereNull('archived_at')->when(app()->environment('production'), fn ($q) => $q->where('is_demo', false)); }
    public function isPubliclyVisible(): bool { return $this->is_published && ! $this->archived_at && (! app()->environment('production') || ! $this->is_demo); }
}
