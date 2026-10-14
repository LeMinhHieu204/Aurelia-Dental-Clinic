<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSpecialty extends Model
{
    protected $fillable = ['name', 'slug'];
    public function doctors() { return $this->belongsToMany(Doctor::class, 'doctor_specialty'); }
}
