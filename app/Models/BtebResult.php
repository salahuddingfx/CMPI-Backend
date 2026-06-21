<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BtebResult extends Model
{
    protected $fillable = [
        'roll',
        'center_code',
        'institute_name',
        'department',
        'semester',
        'regulation',
        'holding_year',
        'gpa',
        'status',
        'exam_type',
        'referred_subjects',
        'raw_text',
    ];

    protected $casts = [
        'referred_subjects' => 'array',
        'gpa' => 'float',
    ];
}
