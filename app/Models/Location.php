<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name', 'longitude', 'latitude', 'status', 'type', 'delivery_distance', 'coordinates'
    ];
}
