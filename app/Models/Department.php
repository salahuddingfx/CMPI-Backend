<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['slug','title','short_title','description','overview','objectives','labs','achievements','career_opportunities'];
    protected $casts = ['objectives'=>'array','labs'=>'array','achievements'=>'array','career_opportunities'=>'array'];
}