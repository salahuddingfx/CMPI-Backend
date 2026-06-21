<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['department', 'semester', 'subject_code', 'subject_name', 'credit'];
}
