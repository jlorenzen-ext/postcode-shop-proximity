<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class Location extends Model
{
    use HasSpatial;

    protected $fillable = [
        'name', 'longitude', 'latitude', 'status', 'type', 'delivery_distance', 'coordinates'
    ];

    protected $casts = [
        'coordinates' => Point::class,
    ];
}
