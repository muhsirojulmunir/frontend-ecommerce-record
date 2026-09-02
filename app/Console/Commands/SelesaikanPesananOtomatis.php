<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\ProductReview;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Menyelesaikan pesanan dan membuat ulasan otomatis bintang 5.
 *
 * Dua tugas:
 *  1. Auto-complete: pesanan 'shipped' >= 3 hari → status 'completed' + ulasan 5 bintang.
 *  2. Auto-review: pesanan 'completed' >= 3 hari yang masih ada item belum dinilai → ulasan 5 bintang.
 */
class SelesaikanPesananOtomatis extends Command
{
    protected $signature   = 'pesanan:selesaikan-otomatis {--dry-run : Tampilkan tanpa menyimpan}';
    protected $description = 'Auto-complete pesanan shipped > 3 hari dan buat ulasan bintang 5 otomatis untuk pesanan completed > 3 hari.';

    private const BATAS_HARI = 3;

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        if ($dryRun) $this->warn('[DRY RUN] Tidak ada data yang disimpan.');

        $this->autoComplete($dryRun);
        $this->autoReview($dryRun);

        return self::SUCCESS;
    }

    private function autoComplete(bool $dryRun): void
    {
        $batas = now()->subDays(self::BATAS_HARI);

        $pesanan = Order::where('status', 'shipped')
            ->where('payment_status', 'paid')
            ->where(function ($q) use ($batas) {
                $q->where(function ($q2) use ($batas) {
                    $q2->whereNotNull('delivered_at')->where('delivered_at', '<=', $batas);
                })->orWhere(function ($q2) use ($batas) {
                    $q2->whereNull('delivered_at')->where('updated_at', '<=', $batas);
                });
            })
            ->get();

        if ($pesanan->isEmpty()) {
            $this->info('[Auto-Complete] Tidak ada pesanan yang perlu diselesaikan otomatis.');
            return;
        }

        $this->info("[Auto-Complete] {$pesanan->count()} pesanan akan diselesaikan otomatis.");

        foreach ($pesanan as $order) {
            $this->line("  -> {$order->order_number}");
            if ($dryRun) continue;

            DB::transaction(function () use ($order) {
                $now = now();
                $order->update(['status' => 'completed', 'completed_at' => $now]);
                $this->buatUlasanOtomatis($order, $now);
            });

            Log::info('Pesanan auto-completed oleh sistem', ['order' => $order->order_number]);
        }
    }

    private function autoReview(bool $dryRun): void
    {
        $batas = now()->subDays(self::BATAS_HARI);

        $pesanan = Order::where('status', 'completed')
            ->where('payment_status', 'paid')
            ->where('completed_at', '<=', $batas)
            ->whereHas('items', fn ($q) => $q->whereDoesntHave('review'))
            ->get();

        if ($pesanan->isEmpty()) {
            $this->info('[Auto-Review] Tidak ada item yang perlu diulas otomatis.');
            return;
        }

        $this->info("[Auto-Review] {$pesanan->count()} pesanan akan diulas otomatis bintang 5.");

        foreach ($pesanan as $order) {
            $this->line("  -> {$order->order_number} (selesai {$order->completed_at->format('d M Y')})");
            if ($dryRun) continue;

            DB::transaction(fn () => $this->buatUlasanOtomatis($order, $order->completed_at));
            Log::info('Auto-review bintang 5 dibuat sistem', ['order' => $order->order_number]);
        }
    }

    private function buatUlasanOtomatis(Order $order, $tanggal): void
    {
        $items = $order->items()->whereDoesntHave('review')->get();

        foreach ($items as $item) {
            ProductReview::firstOrCreate(
                ['order_item_id' => $item->id],
                [
                    'product_id' => $item->product_id,
                    'order_id'   => $order->id,
                    'user_id'    => $order->user_id,
                    'rating'     => 5,
                    'comment'    => null,
                    'photos'     => null,
                    'is_hidden'  => false,
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ]
            );
        }
    }
}