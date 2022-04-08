<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'date',
        'place_departure',
        'place_arrival',
        'km_step',
        'hide',
    ];

    protected $casts = [
        'km_step' => 'float',
        'km_total' => 'float',
        'hide' => 'boolean',
    ];

    public function comments()
    {
        return $this->hasMany(Comment::class)->where('report', '<', 3);
    }

    public function images()
    {
        return $this->hasMany(Image::class)->orderBy('album_order');
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }
}
