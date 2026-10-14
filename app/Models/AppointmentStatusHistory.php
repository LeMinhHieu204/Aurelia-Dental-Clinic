<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentStatusHistory extends Model
{
    protected $fillable = ['appointment_request_id', 'status', 'notes', 'changed_by', 'metadata'];
    protected $casts = ['metadata' => 'array'];
    public function appointment() { return $this->belongsTo(AppointmentRequest::class, 'appointment_request_id'); }
    public function changedBy() { return $this->belongsTo(User::class, 'changed_by'); }
}
