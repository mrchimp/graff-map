<?php

namespace App;

use Grimzy\LaravelMysqlSpatial\Eloquent\Builder;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Grimzy\LaravelMysqlSpatial\Types\LineString;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use SpatialTrait;

    protected $fillable = [
        'title',
        'artist',
        'coords',
        'photo',
    ];

    protected $spatialFields = [
        'coords',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'date',
    ];

    /**
     * Find Photos which have been approved
     *
     * @param Builder $query
     *
     * @return void
     */
    public function scopeAvailable(Builder $query): void
    {
        $query->where('approved', 1);
    }

    /**
     * Find Photos within a given rectangle
     *
     * @param Builder $query
     * @param floar $north north boundd
     * @param float $east east bounds
     * @param float $south south bounds
     * @param float $west west bounds
     *
     * @return void
     */
    public function scopeInRect(Builder $query, $north, $east, $south, $west): void
    {
        $query->within('coords', new Polygon([new LineString([
            new Point($north, $west),
            new Point($north, $east),
            new Point($south, $east),
            new Point($south, $west),
            new Point($north, $west),
        ])]));
    }
}
