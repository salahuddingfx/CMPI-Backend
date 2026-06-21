<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $table = 'faculty';
    protected $fillable = ['name','designation','department','qualification','email','phone','specialization','photo'];
    protected $casts = ['specialization'=>'array'];
}