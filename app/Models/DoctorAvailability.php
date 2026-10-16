<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DoctorAvailability extends Model
{
    protected $table = 'doctor_availability';
    protected $fillable = ['doctor_id', 'location_id', 'available_date', 'weekday', 'starts_at', 'ends_at', 'timezone', 'is_active'];
    protected $casts = ['available_date' => 'date', 'is_active' => 'boolean'];

    public function setAvailableDateAttribute($value): void
    {
        $this->attributes['available_date'] = $value ? Carbon::parse($value)->toDateString() : null;
    }

    public function doctor() { return $this->belongsTo(Doctor::class); }
    public function location() { return $this->belongsTo(Location::class); }
}
