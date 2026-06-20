<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstituteResult extends Model
{
    use HasFactory;

    protected $table = 'institute_results';

    protected $fillable = [
        'roll',
        'semester',
        'academic_year',
        'status',
        'referred_subjects',
        'raw_text',
    ];

    protected $casts = [
        'referred_subjects' => 'array',
    ];
}
