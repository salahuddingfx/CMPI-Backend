<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = ['title','category','description','status','upvotes','user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}