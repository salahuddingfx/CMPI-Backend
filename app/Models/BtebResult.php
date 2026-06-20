<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BtebResult extends Model
{
    use HasFactory;

    protected $table = 'bteb_results';

    protected $fillable = [
        'roll',
        'department',
        'semester',
        'regulation',
        'holding_year',
        'gpa',
        'status',
        'referred_subjects',
        'raw_text',
    ];

    protected $casts = [
        'referred_subjects' => 'array',
        'gpa' => 'decimal:2',
    ];
}
