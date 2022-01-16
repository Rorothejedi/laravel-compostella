<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $casts = ['main_album_image' => 'boolean'];

    public function scopeMainAlbumImage($query)
    {
        return $query->where('main_album_image', 1);
    }
}
