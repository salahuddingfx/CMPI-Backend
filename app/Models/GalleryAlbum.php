<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryAlbum extends Model
{
    protected $table = 'gallery_albums';
    protected $fillable = ['title','count','description','accent','cover'];

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class);
    }
}
