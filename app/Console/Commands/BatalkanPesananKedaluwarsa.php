<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\PembatalanPesananService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Membatalkan pesanan yang melewati batas waktu pembayaran (default 24 jam).
 *
 * Berlaku untuk:
 * 1. Pembayaran otomatis (Midtrans/Gateway) yang belum dibayar atau gagal.
 * 2. Pembayaran manual (Transfer Bank / dsb) yang belum dibayar dan belum mengunggah bukti transfer.
 *
 * Efek:
 * - Status pesanan diubah menjadi 'cancelled'.
 * - Stok barang (produk & varian) otomatis dikembalikan.
 * - Alasan pembatalan dan timestamp tercatat rapi.
 */
class BatalkanPesananKedaluwarsa extends Command
{
    protected $signature = 'pesanan:batalkan-kedaluwarsa 
                            {--hours=24 : Batas waktu toleransi pembayaran dalam jam} 
                            {--dry-run : Menampilkan pesanan tanpa mengubah status}';

    protected $description = 'Batalkan pesanan pending yang belum dibayar melebihi batas waktu (24 jam) dan kembalikan stok.';

    public function handle(PembatalanPesananService $pembatalanService): int
    {
        $hours = (int) $this->option('hours');
        if ($hours <= 0) {
            $hours = 24;
        }

        $dryRun = (bool) $this->option('dry-run');
        if ($dryRun) {
            $this->warn('[DRY RUN] Mode uji coba aktif: tidak ada data yang akan diubah.');
        }

        $batasWaktu = now()->subHours($hours);

        // Cari pesanan riil yang belum dibayar dan sudah lewat dari batas waktu
        $orders = Order::with(['items.product', 'items.productVariant', 'user'])
            ->where('status', 'pending')
            ->where('is_fake', false)
            ->where('created_at', '<=', $batasWaktu)
            ->where(function ($q) {
                $q->whereIn('payment_status', ['unpaid', 'failed'])
                  ->orWhere(function ($q2) {
                      $q2->where('payment_status', 'unpaid')
                         ->whereNull('payment_proof');
                  });
            })
            ->where('payment_status', '!=', 'pending_verification')
            ->get();

        if ($orders->isEmpty()) {
            $this->info("[Auto-Cancel] Tidak ada pesanan pending yang melewati batas waktu {$hours} jam.");
            return self::SUCCESS;
        }

        $this->info("[Auto-Cancel] Ditemukan {$orders->count()} pesanan yang melewati batas waktu {$hours} jam.");

        foreach ($orders as $order) {
            $this->line("  -> Membatalkan pesanan #{$order->order_number} (Dibuat: {$order->created_at->format('d M Y H:i')}, Metode: {$order->payment_method})");

            if ($dryRun) {
                continue;
            }

            try {
                $pembatalanService->batalkan(
                    $order,
                    'Waktu pembayaran telah habis (Kedaluwarsa)',
                    "Sistem otomatis membatalkan pesanan karena telah melewati batas waktu pembayaran {$hours} jam."
                );

                Log::info('Pesanan otomatis dibatalkan karena kedaluwarsa', [
                    'order_number'   => $order->order_number,
                    'created_at'     => $order->created_at->toDateTimeString(),
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                ]);
            } catch (\Throwable $e) {
                $this->error("  Gagal membatalkan #{$order->order_number}: " . $e->getMessage());
                Log::error('Gagal auto-cancel pesanan kedaluwarsa: ' . $e->getMessage(), [
                    'order' => $order->order_number,
                ]);
            }
        }

        $this->info("[Auto-Cancel] Selesai memproses {$orders->count()} pesanan kedaluwarsa.");

        return self::SUCCESS;
    }
}