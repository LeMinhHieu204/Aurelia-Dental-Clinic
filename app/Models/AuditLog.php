<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'action', 'auditable_type', 'auditable_id', 'metadata', 'ip_hash'];
    protected $casts = ['metadata' => 'array'];
    public function user() { return $this->belongsTo(User::class); }
    public static function record(string $action, ?Model $model = null, array $metadata = []): self
    {
        return static::create(['user_id' => auth()->id(), 'action' => $action, 'auditable_type' => $model ? $model::class : null, 'auditable_id' => $model?->getKey(), 'metadata' => $metadata, 'ip_hash' => request()?->ip() ? hash('sha256', request()->ip()) : null]);
    }
}
