<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Postcode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'longitude' => 'required|numeric|between:-180,180',
            'latitude' => 'required|numeric|between:-90,90',
            'status' => ['required', Rule::in(['open', 'closed'])],
            'type' => ['required', Rule::in(['takeaway', 'shop', 'restaurant'])],
            'delivery_distance' => 'required|integer|min:0'
        ]);

        $location = Location::create([
            'name' => $validatedData['name'],
            'coordinates' => null, // TODO
            'longitude' => $validatedData['longitude'],
            'latitude' => $validatedData['latitude'],
            'status' => $validatedData['status'],
            'type' => $validatedData['type'],
            'delivery_distance' => $validatedData['delivery_distance'],
        ]);

        return response()->json([
            'message' => 'Successfully created new location.',
            'location' => $location
        ], 201);
    }

    public function list(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'postcode' => 'required|string|exists:postcodes,postcode',
            'radius' => 'required|integer|min:1'
        ]);

        $postcode = Postcode::where('postcode', $validatedData['postcode'])->first();
        if (!$postcode) {
            return response()->json(['message' => 'Postcode not found'], 404);
        }

        $locations = Location::whereRaw(
            "ST_Distance_Sphere(coordinates, ST_GeomFromText(?)) <= ?",
            [
                "POINT({$postcode->longitude} {$postcode->latitude})",
                $validatedData['radius']
            ]
        )->get();

        return response()->json([$locations]);
    }

    public function search(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'postcode' => 'required|string|exists:postcodes,postcode'
        ]);

        $postcode = Postcode::where('postcode', $validatedData['postcode'])->first();
        if (!$postcode) {
            return response()->json(['message' => 'Postcode not found'], 404);
        }

        $locations = Location::where('status', 'open')
            ->whereRaw(
                "ST_Distance_Sphere(coordinates, ST_GeomFromText(?)) <= delivery_distance",
                ["POINT({$postcode->longitude} {$postcode->latitude})"]
            )
            ->get();

        return response()->json([$locations]);
    }
}
