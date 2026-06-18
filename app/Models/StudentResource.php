<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentResource extends Model
{
    protected $table = 'student_resources';
    protected $fillable = ['title','type','description','updated_at_date'];
}