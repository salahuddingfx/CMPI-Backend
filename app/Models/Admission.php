<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admission extends Model
{
    protected $fillable = [
        'application_id', 'name', 'email', 'phone', 'department',
        'session', 'ssc_gpa', 'hsc_gpa', 'father_name', 'mother_name',
        'address', 'blood_group', 'documents', 'status',
    ];

    protected $casts = [
        'documents' => 'array',
    ];
}