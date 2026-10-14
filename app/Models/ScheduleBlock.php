<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleBlock extends Model
{
    protected $fillable = ['doctor_id', 'location_id', 'blocked_date', 'starts_at', 'ends_at', 'reason'];
    public function doctor() { return $this->belongsTo(Doctor::class); }
    public function location() { return $this->belongsTo(Location::class); }
}
