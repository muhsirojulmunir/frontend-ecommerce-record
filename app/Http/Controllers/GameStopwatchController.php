<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameStopwatchController extends Controller
{
    // ─── Konfigurasi Tier Hadiah ─────────────────────────────────────────────
    private const TIER_PERFECT = [
        'key'    => 'perfect',
        'label'  => 'PERFECT 10.00s! 🎯',
        'title'  => 'Luar Biasa Sempurna!',
        'amount' => 50000,
        'min'    => 9.98,
        'max'    => 10.02,
        'badge'  => 'Gold Tier',
        'color'  => 'amber',
    ];

    private const TIER_NEAR = [
        'key'    => 'near',
        'label'  => 'HAMPIR SEMPURNA! ⚡',
        'title'  => 'Bagus Sekali, Nyaris Pas!',
        'amount' => 30000,
        'min'    => 9.85,
        'max'    => 10.15,
        'badge'  => 'Violet Tier',
        'color'  => 'violet',
    ];

    private const TIER_GOOD = [
        'key'    => 'good',
        'label'  => 'PARTISIPASI HEBAT! 👏',
        'title'  => 'Terima Kasih Sudah Mencoba!',
        'amount' => 10000,
        'min'    => 0,
        'max'    => PHP_FLOAT_MAX,
        'badge'  => 'Emerald Tier',
        'color'  => 'emerald',
    ];

    // ─── Status (Cek kesempatan main hari ini) ────────────────────────────────
    public function status(Request $request): JsonResponse
    {
        if (! auth()->check()) {
            return response()->json([
                'can_play'         => false,
                'is_authenticated' => false,
                'reason'           => 'unauthenticated',
                'message'          => 'Silakan login terlebih dahulu untuk mengikuti Stopwatch Challenge.',
            ]);
        }

        $user  = auth()->user();
        $today = now()->toDateString();

        $playedToday = false;
        if (! empty($user->last_game_at)) {
            $lastDate = is_string($user->last_game_at) 
                ? substr($user->last_game_at, 0, 10) 
                : $user->last_game_at->toDateString();
            $playedToday = ($lastDate === $today);
        }

        if ($playedToday) {
            $nextPlay = now()->addDay()->startOfDay();
            return response()->json([
                'can_play'         => false,
                'is_authenticated' => true,
                'reason'           => 'already_played',
                'next_play_at'     => $nextPlay->toISOString(),
                'message'          => 'Kamu sudah bermain hari ini. Kesempatan baru dibuka besok pukul 00:00!',
            ]);
        }

        return response()->json([
            'can_play'         => true,
            'is_authenticated' => true,
            'message'          => 'Siap bermain! Target kamu 10.00 detik.',
        ]);
    }

    // ─── Claim (Proses hasil stopwatch & reservasi voucher) ─────────────────
    public function claim(Request $request): JsonResponse
    {
        if (! auth()->check()) {
            return response()->json([
                'success' => false, 
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }

        $request->validate([
            'elapsed_time' => 'required|numeric|min:0|max:60',
        ]);

        $user  = auth()->user();
        $today = now()->toDateString();

        $playedToday = false;
        if (! empty($user->last_game_at)) {
            $lastDate = is_string($user->last_game_at) 
                ? substr($user->last_game_at, 0, 10) 
                : $user->last_game_at->toDateString();
            $playedToday = ($lastDate === $today);
        }

        if ($playedToday) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu sudah bermain hari ini. Coba lagi besok!',
            ], 422);
        }

        $elapsed = round((float) $request->elapsed_time, 2);
        $tier    = $this->resolveTier($elapsed);

        // Catat tanggal main user
        $user->update([
            'last_game_at' => $today,
        ]);

        // Cari voucher tersedia untuk tier ini dari Kelola Voucher
        $voucher = Voucher::availableForGame($tier['amount'])->inRandomOrder()->first();

        if (! $voucher) {
            return response()->json([
                'success'       => true,
                'has_voucher'   => false,
                'tier'          => $tier['label'],
                'tier_key'      => $tier['key'],
                'tier_title'    => $tier['title'],
                'target_amount' => $tier['amount'],
                'elapsed_time'  => $elapsed,
                'diff'          => round($elapsed - 10.00, 2),
                'message'       => 'Stok voucher Rp ' . number_format($tier['amount'], 0, ',', '.') . ' di Kelola Voucher sedang kosong. Nantikan restock berikutnya!',
            ]);
        }

        // Reservasi 5 menit agar voucher aman sementara
        $voucher->reserveFor($user->id);

        return response()->json([
            'success'       => true,
            'has_voucher'   => true,
            'tier'          => $tier['label'],
            'tier_key'      => $tier['key'],
            'tier_title'    => $tier['title'],
            'target_amount' => $tier['amount'],
            'elapsed_time'  => $elapsed,
            'diff'          => round($elapsed - 10.00, 2),
            'voucher'       => [
                'code'             => $voucher->code,
                'amount'           => $voucher->amount,
                'formatted_amount' => 'Rp ' . number_format($voucher->amount, 0, ',', '.'),
                'expires_at'       => $voucher->reserved_until?->toISOString(),
            ],
        ]);
    }

    // ─── Save (User simpan voucher secara permanen ke akun) ─────────────────
    public function save(Request $request): JsonResponse
    {
        if (! auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'voucher_code' => 'required|string|max:20',
        ]);

        $user    = auth()->user();
        $voucher = Voucher::where('code', strtoupper(trim($request->voucher_code)))
            ->where('reserved_for', $user->id)
            ->where('reserved_until', '>=', now())
            ->where('is_used', false)
            ->first();

        if (! $voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher tidak ditemukan atau batas waktu penyimpanan (5 menit) telah lewat.',
            ], 422);
        }

        $voucher->claimByUser($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil disimpan ke akun kamu! Gunakan kode saat checkout belanja.',
            'voucher' => [
                'code'             => $voucher->code,
                'amount'           => $voucher->amount,
                'formatted_amount' => 'Rp ' . number_format($voucher->amount, 0, ',', '.'),
            ],
        ]);
    }

    // ─── Discard (User tutup modal tanpa simpan -> voucher dibebaskan) ─────
    public function discard(Request $request): JsonResponse
    {
        if (! auth()->check()) {
            return response()->json(['success' => false], 401);
        }

        $code = $request->input('voucher_code');
        if ($code) {
            $voucher = Voucher::where('code', strtoupper(trim($code)))
                ->where('reserved_for', auth()->id())
                ->first();

            if ($voucher) {
                $voucher->releaseReservation();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Reservasi voucher telah dibatalkan.',
        ]);
    }

    // ─── Helper Penentu Tier ─────────────────────────────────────────────────
    private function resolveTier(float $elapsed): array
    {
        if ($elapsed >= self::TIER_PERFECT['min'] && $elapsed <= self::TIER_PERFECT['max']) {
            return self::TIER_PERFECT;
        }

        if ($elapsed >= self::TIER_NEAR['min'] && $elapsed <= self::TIER_NEAR['max']) {
            return self::TIER_NEAR;
        }

        return self::TIER_GOOD;
    }
}
