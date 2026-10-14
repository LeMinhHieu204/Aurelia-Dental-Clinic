<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    protected $fillable = ['filename', 'path', 'disk', 'mime_type', 'size_bytes', 'width', 'height', 'alt_text', 'license', 'attribution', 'source', 'sha256', 'is_public', 'uploaded_by'];
    protected $casts = ['is_public' => 'boolean'];
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
}
