<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['approved_name', 'location_label', 'body', 'rating', 'review_date', 'source', 'permission_granted', 'is_published', 'is_demo'];
    protected $casts = ['review_date' => 'date', 'permission_granted' => 'boolean', 'is_published' => 'boolean', 'is_demo' => 'boolean'];
    public function scopePublicVisible(Builder $query): Builder { return $query->where('is_published', true)->where('permission_granted', true)->when(app()->environment('production'), fn ($q) => $q->where('is_demo', false)); }
}
