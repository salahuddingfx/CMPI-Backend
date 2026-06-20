<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institute extends Model
{
    protected $fillable = [
        'name', 'short_name', 'tagline', 'address', 'phone',
        'email', 'website', 'logo', 'eiin', 'established',
    ];
}
