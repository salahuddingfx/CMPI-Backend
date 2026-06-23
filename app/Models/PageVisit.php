<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageVisit extends Model
{
    protected $fillable = [
        'visitor_id',
        'ip_address',
        'country',
        'city',
        'region',
        'isp',
        'page_url',
        'referrer',
        'user_agent',
        'device_type',
        'browser',
        'os',
    ];
}
