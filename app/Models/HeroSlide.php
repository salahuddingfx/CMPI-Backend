<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    protected $fillable = [
        'eyebrow',
        'title',
        'description',
        'image',
        'video_url',
        'cta_label',
        'cta_href',
        'secondary_label',
        'secondary_href',
        'panel_title',
        'panel_description',
        'stats',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'stats' => 'array',
        'is_active' => 'boolean',
    ];
}
