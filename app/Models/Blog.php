<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = ['slug','title','excerpt','content','author','date','category','read_time','related_ids','image'];
    protected $casts = ['related_ids'=>'array','date'=>'date'];
}