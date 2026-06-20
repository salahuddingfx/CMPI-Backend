<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportJob extends Model
{
    use HasFactory;

    protected $table = 'import_jobs';

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
