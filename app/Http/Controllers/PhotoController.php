<?php

namespace App\Http\Controllers;

use App\Photo;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Intervention\Image\ImageManagerStatic as Image;

class PhotoController extends Controller
{
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
            ->save(storage_path('app/photos/' . $filename . '.jpg'), 80, 'jpg');

        $photo = Photo::create([
            'title' => $request->input('title'),
            'artist' => $request->input('artist'),
            'photo' => $filename,
            'coords' => new Point($request->input('lat'), $request->input('lng')),
        ]);

        return response()->json('Ok', 201);
    }
}
