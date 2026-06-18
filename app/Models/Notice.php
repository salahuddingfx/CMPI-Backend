<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $fillable = ['title','category','date','summary','details','file_url','image'];
    protected $casts = ['date'=>'date'];
}