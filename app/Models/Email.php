<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    protected $fillable = ['from_email','to_email','cc','subject','preview','body','date','folder','read','starred','label'];
    protected $casts = ['read'=>'boolean','starred'=>'boolean','date'=>'date'];
}