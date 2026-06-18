<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryAlbum extends Model
{
    protected $table = 'gallery_albums';
    protected $fillable = ['title','count','description','accent'];
}