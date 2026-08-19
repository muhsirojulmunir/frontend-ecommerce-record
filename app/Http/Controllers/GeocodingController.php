<?php

namespace App\Http\Controllers;

use App\Services\GeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Perantara pencarian koordinat untuk halaman checkout.
 *
 * Lihat alasannya di App\Services\GeocodingService.
 */
class GeocodingController extends Controller
{
    public function __construct(private GeocodingService $geocoding)
    {
    }

    public function cari(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => 'required|string|max:300',
        ]);

        return response()->json(['hasil' => $this->geocoding->cari($data['q'])]);
    }

    public function balik(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        return response()->json([
            'hasil' => $this->geocoding->balik((float) $data['lat'], (float) $data['lng']),
        ]);
    }
}
