<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = ['full_name', 'email', 'phone'];
    public function appointments() { return $this->hasMany(AppointmentRequest::class); }
}
