<?php

namespace App\Http\Controllers;

use App\Photo;
use Grimzy\LaravelMysqlSpatial\Types\LineString;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

class PhotoController extends Controller
{
    public function index(Request $request)
    {
        $this->validate($request, [
            'lat' => 'required|numeric|min:-90|max:90',
            'lng' => 'required|numeric|min:-180|max:180',
        ]);

        return response()->json([
            'photos' => Photo::query()
                ->equals('coords', new Point(
                    $request->input('lat'),
                    $request->input('lng')
                ))
                ->available()
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string',
            'artist' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'photo' => 'required|image',
        ]);

        $filename = uniqid(Str::slug($request->input('title')));

        Image::make($request->file('photo'))
            ->save(storage_path('app/public/photos/' . $filename . '.jpg'), 80, 'jpg');

        $photo = Photo::create([
            'title' => $request->input('title'),
            'artist' => $request->input('artist'),
            'photo' => $filename,
            'coords' => new Point($request->input('lat'), $request->input('lng')),
        ]);

        return response()->json('Ok', 201);
    }
}
