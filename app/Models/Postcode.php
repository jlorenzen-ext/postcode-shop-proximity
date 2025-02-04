<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class Postcode extends Model
{
    use HasSpatial;

    protected $fillable = [
        'coordinates', 'longitude', 'latitude', 'postcode'
    ];

    protected $casts = [
        'coordinates' => Point::class,
    ];
}
