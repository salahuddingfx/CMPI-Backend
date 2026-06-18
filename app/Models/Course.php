<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['code','title','instructor','user_id','progress','attendance','next_class'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}