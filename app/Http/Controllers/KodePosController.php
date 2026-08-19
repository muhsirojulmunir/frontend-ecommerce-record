<?php

namespace App\Http\Controllers;

use App\Services\KodePosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Pilihan kode pos untuk halaman checkout.
 *
 * Lihat alasan pemilihan sumbernya di App\Services\KodePosService.
 */
class KodePosController extends Controller
{
    public function __construct(private KodePosService $kodePos)
    {
    }

    public function cari(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => 'required|string|max:150',
        ]);

        return response()->json(['pilihan' => $this->kodePos->cari($data['q'])]);
    }
}
