<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;

class Photo extends Model
{
    use SpatialTrait;

    protected $fillable = [
        'title',
        'artist',
        'coords',
        'photo',
    ];

    protected $spatial_fields = [
        'coords',
    ];
}
