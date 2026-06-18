<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $fillable = ['user_id','title','amount','due','status'];
    protected $casts = ['amount'=>'decimal:2','due'=>'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}