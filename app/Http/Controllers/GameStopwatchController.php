<?php

namespace App\Http\Controllers;

use App\Models\GameSetting;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameStopwatchController extends Controller
{
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
        'max'    => 999.0,
        'badge'  => 'Emerald Tier',
        'color'  => 'emerald',
    ];

    // ─── Status (Cek kesempatan main hari ini) ────────────────────────────────
    public function status(Request $request): JsonResponse
    {
        $gameSetting = GameSetting::forGame('stopwatch');
        if ($gameSetting && ! $gameSetting->is_active) {
            return response()->json([
                'can_play'         => false,
                'is_active'        => false,
                'is_authenticated' => auth()->check(),
                'reason'           => 'game_disabled',
                'message'          => 'Tantangan Stopwatch saat ini sedang dinonaktifkan sementara oleh admin.',
            ]);
        }

        if (! auth()->check()) {
            return response()->json([
                'can_play'         => false,
                'is_active'        => true,
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
            $secondsUntilNextPlay = max(0, now()->diffInSeconds($nextPlay));

            return response()->json([
                'can_play'                => false,
                'is_active'               => true,
                'is_authenticated'        => true,
                'reason'                  => 'already_played',
                'next_play_at'            => $nextPlay->toISOString(),
                'seconds_until_next_play' => $secondsUntilNextPlay,
                'message'                 => 'Kamu sudah bermain hari ini. Kesempatan baru dibuka besok pukul 00:00!',
            ]);
        }

        return response()->json([
            'can_play'         => true,
            'is_active'        => true,
            'is_authenticated' => true,
            'message'          => 'Siap bermain! Target kamu 10.00 detik.',
        ]);
    }

    // ─── Claim (Proses hasil stopwatch & reservasi voucher) ─────────────────
    public function claim(Request $request): JsonResponse
    {
        $gameSetting = GameSetting::forGame('stopwatch');
        if ($gameSetting && ! $gameSetting->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Tantangan Stopwatch saat ini sedang dinonaktifkan sementara.',
            ], 422);
        }

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

        // Bersihkan reservasi kadaluarsa sebelum mencari voucher
        Voucher::releaseExpiredReservations();

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
                'success'                 => false,
                'can_play'                => false,
                'next_play_at'            => $nextPlay->toISOString(),
                'seconds_until_next_play' => max(0, now()->diffInSeconds($nextPlay)),
                'message'                 => 'Kamu sudah bermain hari ini. Coba lagi besok!',
            ], 422);
        }

        $elapsed = round((float) $request->elapsed_time, 2);
        $tier    = $this->resolveTier($elapsed, $gameSetting);

        // Catat tanggal main user langsung ke database
        DB::table('users')->where('id', $user->id)->update([
            'last_game_at' => $today,
        ]);
        $user->last_game_at = $today;

        // Jika tier nominal 0 (Zonk / tidak ada hadiah voucher)
        if ($tier['amount'] <= 0) {
            return response()->json([
                'success'       => true,
                'has_voucher'   => false,
                'tier'          => $tier['label'],
                'tier_key'      => $tier['key'],
                'tier_title'    => $tier['title'],
                'target_amount' => 0,
                'elapsed_time'  => $elapsed,
                'diff'          => round($elapsed - 10.00, 2),
                'message'       => 'Terima kasih telah berpartisipasi! Coba lagi besok untuk memenangkan voucher diskon.',
            ]);
        }

        // Cari voucher tersedia untuk tier ini dari Kelola Voucher
        $targetCategoryId = $gameSetting ? $gameSetting->getConfigValue('category_id') : null;
        $voucherQuery = Voucher::availableForGame($tier['amount'])->with('category');

        if (!empty($targetCategoryId)) {
            $voucherQuery->where('category_id', $targetCategoryId);
        }

        $voucher = $voucherQuery->inRandomOrder()->first();

        // Jika stok voucher dengan target kategori kosong, cari dari voucher universal sebagai fallback
        if (! $voucher && !empty($targetCategoryId)) {
            $voucher = Voucher::availableForGame($tier['amount'])
                ->with('category')
                ->whereNull('category_id')
                ->inRandomOrder()
                ->first();
        }

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

        $hasExpiry = ! empty($voucher->expires_at);
        $expiresAtHuman = $hasExpiry 
            ? $voucher->expires_at->format('d M Y, H:i') . ' WIB'
            : 'Tanpa Batas Waktu (Selamanya)';

        $categoryName = $voucher->category ? $voucher->category->name : null;
        $scopeLabel   = $categoryName ? 'Khusus Kategori: ' . $categoryName : 'Berlaku untuk: Semua Produk';

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
                'code'                 => $voucher->code,
                'amount'               => $voucher->amount,
                'formatted_amount'     => 'Rp ' . number_format($voucher->amount, 0, ',', '.'),
                'category_id'          => $voucher->category_id,
                'category_name'        => $categoryName,
                'scope_label'          => $scopeLabel,
                'expires_at'           => $voucher->expires_at?->toISOString(),
                'expires_at_human'     => $expiresAtHuman,
                'has_expiry'           => $hasExpiry,
                'reservation_deadline' => $voucher->reserved_until?->toISOString(),
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
                'message' => 'Voucher tidak ditemukan atau masa klaim 5 menit telah berakhir.',
            ], 422);
        }

        $voucher->claimPermanently($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil disimpan ke akun kamu!',
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
    private function resolveTier(float $elapsed, ?GameSetting $gameSetting = null): array
    {
        $configTiers = $gameSetting?->getConfigValue('tiers');

        $perfect = array_merge(self::TIER_PERFECT, $configTiers['perfect'] ?? []);
        $near    = array_merge(self::TIER_NEAR, $configTiers['near'] ?? []);
        $good    = array_merge(self::TIER_GOOD, $configTiers['good'] ?? []);

        if ($elapsed >= (float)$perfect['min'] && $elapsed <= (float)$perfect['max']) {
            return $perfect;
        }

        if ($elapsed >= (float)$near['min'] && $elapsed <= (float)$near['max']) {
            return $near;
        }

        return $good;
    }
}
