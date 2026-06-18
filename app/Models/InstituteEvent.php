<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstituteEvent extends Model
{
    protected $table = 'events';
    protected $fillable = ['title','date','end_date','time','venue','category','status','summary','details','image'];
    protected $casts = ['date'=>'date','end_date'=>'date'];
}