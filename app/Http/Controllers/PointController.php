<?php

namespace App\Http\Controllers;

use App\Photo;
use Illuminate\Http\Request;

class PointController extends Controller
{
    public function index(Request $request)
    {
        $this->validate($request, [
            'north' => 'required|numeric|min:-90|max:90',
            'south' => 'required|numeric|min:-90|max:90',
            'east' => 'required|numeric|min:-180|max:180',
            'west' => 'required|numeric|min:-180|max:180',
        ]);

        $points = Photo::query()
            ->inRect(
                $request->input('north'),
                $request->input('east'),
                $request->input('south'),
                $request->input('west')
            )
            ->available()
            ->get()
            ->pluck('coords')
            ->unique();

        return response()->json([
            'points' => $points,
        ]);
    }
}
