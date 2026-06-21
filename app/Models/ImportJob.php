<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportJob extends Model
{
    protected $fillable = [
        'drive_url',
        'semester',
        'regulation',
        'holding_year',
        'status',
        'total_files',
        'processed_files',
        'total_results',
        'error_log',
    ];

    protected $casts = [
        'error_log' => 'array',
    ];
}
