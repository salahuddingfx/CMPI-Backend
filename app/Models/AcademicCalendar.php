<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AcademicCalendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'event_date',
        'end_date',
        'category',
        'description',
        'is_holiday',
    ];

    protected $casts = [
        'event_date' => 'date',
        'end_date'   => 'date',
        'is_holiday' => 'boolean',
    ];
}
