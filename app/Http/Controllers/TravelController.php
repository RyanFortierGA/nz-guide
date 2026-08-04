<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelController extends Controller
{
    /**
     * Return Airbnb + flight deep links for a location.
     * Real-time listing/fare APIs need partner keys — these open curated searches.
     */
    public function links(Request $request, Location $location): JsonResponse
    {
        $origin = $request->user()?->home_airport
            ?: $request->string('origin')->toString()
            ?: 'AKL';

        return response()->json([
            'airbnb' => [
                'url' => $location->airbnbSearchUrl(),
                'label' => 'Browse Airbnb stays near '.$location->name,
            ],
            'flights' => $location->airport_code ? [
                'url' => $location->googleFlightsUrl($origin),
                'label' => "Flights {$origin} → {$location->airport_code}",
                'origin' => $origin,
                'destination' => $location->airport_code,
            ] : null,
            'kayak' => $location->airport_code ? [
                'url' => "https://www.kayak.com/flights/{$origin}-{$location->airport_code}/",
                'label' => 'Compare on Kayak',
            ] : null,
        ]);
    }
}
