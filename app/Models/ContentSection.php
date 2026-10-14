<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ContentSection extends Model
{
    protected $fillable = ['key', 'title', 'body', 'is_published', 'sort_order', 'seo_title', 'seo_description', 'published_at', 'updated_by', 'image_url'];
    protected $casts = ['is_published' => 'boolean', 'published_at' => 'datetime'];
    public function editor() { return $this->belongsTo(User::class, 'updated_by'); }
    public function scopePublicVisible(Builder $query): Builder { return $query->where('is_published', true)->whereNotNull('published_at'); }
}
