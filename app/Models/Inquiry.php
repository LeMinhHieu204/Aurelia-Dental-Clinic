<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    public const STATUS_NEW = 'NEW';
    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const STATUS_RESOLVED = 'RESOLVED';
    public const STATUS_SPAM = 'SPAM';
    protected $fillable = ['name', 'email', 'phone', 'subject', 'message', 'status', 'internal_notes', 'assigned_to'];
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
}
