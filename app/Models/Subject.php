<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'department',
        'semester',
        'technology_code',
        'technology_name',
        'subject_code',
        'subject_name',
        'credit',
        'theory_marks',
        'practical_marks',
        'total_marks',
    ];
}
