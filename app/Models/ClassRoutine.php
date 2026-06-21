<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRoutine extends Model
{
    use HasFactory;

    protected $fillable = [
        'department',
        'semester',
        'academic_year',
        'title',
        'pdf_path',
        'original_name',
    ];
}
