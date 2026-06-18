<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseResult extends Model
{
    protected $fillable = ['user_id','semester','sgpa','courses'];
    protected $casts = ['courses'=>'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}