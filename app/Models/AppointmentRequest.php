<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentRequest extends Model
{
    public const STATUS_REQUESTED = 'REQUESTED';
    public const STATUS_CONTACTED = 'CONTACTED';
    public const STATUS_PROPOSED = 'PROPOSED';
    public const STATUS_CONFIRMED = 'CONFIRMED';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CANCELLED = 'CANCELLED';
    public const STATUS_NO_SHOW = 'NO_SHOW';
    protected $fillable = ['patient_id', 'treatment_id', 'doctor_id', 'location_id', 'reference', 'preferred_date', 'preferred_time', 'status', 'message', 'privacy_acknowledgement', 'source_ip_hash', 'scheduled_start_utc', 'scheduled_end_utc', 'timezone', 'internal_notes', 'confirmed_at'];
    protected $casts = ['preferred_date' => 'date', 'privacy_acknowledgement' => 'boolean', 'scheduled_start_utc' => 'datetime', 'scheduled_end_utc' => 'datetime', 'confirmed_at' => 'datetime'];
    public function patient() { return $this->belongsTo(Patient::class); }
    public function treatment() { return $this->belongsTo(Treatment::class); }
    public function doctor() { return $this->belongsTo(Doctor::class); }
    public function location() { return $this->belongsTo(Location::class); }
    public function statusHistory() { return $this->hasMany(AppointmentStatusHistory::class); }
}
