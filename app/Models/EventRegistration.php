<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'email',
        'phone',
        'roll_no',
        'department',
        'semester',
    ];

    public function event()
    {
        return $this->belongsTo(InstituteEvent::class, 'event_id');
    }
}
