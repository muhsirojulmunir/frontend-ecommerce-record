<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Penerima notifikasi pengiriman dari Biteship.
 *
 * Sebelum ini, status pengiriman hanya diketahui dengan BERTANYA ke Biteship
 * setiap kali halaman pelacakan dibuka — dan tiap pertanyaan memotong saldo
 * Rp 10. Dengan webhook, Biteship yang mengabari duluan setiap kali statusnya
 * berubah, gratis, dan pembeli melihat keadaan terbaru tanpa perlu menyegarkan
 * halaman.
 *
 * Tiga peristiwa yang dikirim Biteship:
 *   order.status      — status pengiriman berubah
 *   order.waybill_id  — nomor resi berubah (terjadi saat paket dioper antar kurir)
 *   order.price       — ongkir disesuaikan karena berat sebenarnya berbeda
 *
 * CATATAN KEAMANAN
 * Biteship belum menyediakan tanda tangan digital (HMAC) untuk notifikasinya —
 * dikonfirmasi langsung oleh tim mereka. Yang tersedia hanya Custom Header yang
 * kita tentukan sendiri saat mendaftarkan webhook, lalu dicocokkan di sini.
 *
 * Perlindungannya karena itu lebih lemah daripada webhook Midtrans: siapa pun
 * yang mengetahui nilai header itu bisa berpura-pura menjadi Biteship. Maka
 * yang boleh diubah lewat jalur ini dibatasi ketat pada status pengiriman —
 * TIDAK ADA nilai uang, status pembayaran, maupun kepemilikan pesanan yang
 * bisa disentuh dari sini. Ongkir yang berubah pun hanya dicatat, tidak
 * mengubah tagihan pembeli.
 */
class BiteshipWebhookController extends Controller
{
    /**
     * Status Biteship yang berarti paket sudah sampai.
     */
    private const SAMPAI = ['delivered'];

    /**
     * Status Biteship yang berarti perjalanan berakhir tidak normal.
     * Pesanannya tidak diubah otomatis — kasus seperti ini perlu dilihat admin.
     */
    private const GAGAL = [
        'rejected', 'courierNotFound', 'returned', 'cancelled', 'disposed', 'onHold',
    ];

    public function __invoke(Request $request)
    {
        if (! $this->sahkan($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $peristiwa = (string) $request->input('event');
        $idBiteship = trim((string) $request->input('order_id'));
        $resi = trim((string) $request->input('courier_waybill_id'));
        $status = trim((string) $request->input('status'));

        Log::info('Webhook Biteship diterima', [
            'peristiwa' => $peristiwa,
            'pesanan'   => $idBiteship,
            'status'    => $status,
        ]);

        $order = $this->cariPesanan($idBiteship, $resi);

        if (! $order) {
            /*
             * Dijawab 200, bukan 404. Biteship mengirim ulang notifikasi yang
             * gagal, dan pesanan yang memang bukan milik kita tidak akan pernah
             * ketemu berapa kali pun diulang — hanya membebani kedua sisi.
             */
            Log::warning('Webhook Biteship untuk pesanan yang tidak dikenali', [
                'pesanan' => $idBiteship,
                'resi'    => $resi,
            ]);

            return response()->json(['message' => 'Order not found, ignored']);
        }

        match ($peristiwa) {
            'order.waybill_id' => $this->perbaruiResi($order, $resi),
            'order.price'      => $this->catatOngkirBaru($order, $request),
            default            => null,
        };

        if ($status !== '') {
            $this->perbaruiStatus($order, $status);
        }

        // Jawaban pelacakan yang tersinggah sudah usang begitu statusnya berubah.
        $this->bersihkanSinggahan($order);

        return response()->json(['message' => 'OK']);
    }

    /**
     * Memeriksa Custom Header yang kita tentukan sendiri di dasbor Biteship.
     *
     * Bila BITESHIP_WEBHOOK_TOKEN belum diisi, notifikasi DITOLAK — bukan
     * diterima begitu saja. Membiarkan pintu terbuka karena kuncinya belum
     * dipasang adalah cara paling umum sebuah webhook disalahgunakan.
     */
    private function sahkan(Request $request): bool
    {
        $token = (string) config('services.biteship.webhook_token', '');

        if ($token === '') {
            Log::error('Webhook Biteship ditolak: BITESHIP_WEBHOOK_TOKEN belum diatur.');

            return false;
        }

        $dikirim = (string) $request->header('X-Biteship-Token', '');

        // Dibandingkan dengan hash_equals supaya lamanya perbandingan tidak
        // membocorkan berapa banyak karakter awal yang sudah benar.
        return $dikirim !== '' && hash_equals($token, $dikirim);
    }

    /**
     * Mencari pesanan dari id Biteship, atau dari nomor resinya.
     *
     * Id Biteship dipakai lebih dulu karena tidak pernah berubah. Nomor resi
     * bisa berganti saat paket dioper antar kurir, jadi hanya jadi cadangan
     * untuk pesanan lama yang id-nya belum tersimpan.
     */
    private function cariPesanan(string $idBiteship, string $resi): ?Order
    {
        if ($idBiteship !== '') {
            $order = Order::where('biteship_order_id', $idBiteship)->first();

            if ($order) {
                return $order;
            }
        }

        if ($resi !== '') {
            return Order::where('tracking_number', $resi)->first();
        }

        return null;
    }

    /**
     * Nomor resi berganti — terjadi saat paket dioper ke kurir lanjutan.
     */
    private function perbaruiResi(Order $order, string $resiBaru): void
    {
        if ($resiBaru === '' || $resiBaru === $order->tracking_number) {
            return;
        }

        Log::info('Nomor resi diperbarui lewat webhook Biteship', [
            'pesanan'    => $order->order_number,
            'sebelumnya' => $order->tracking_number,
            'menjadi'    => $resiBaru,
        ]);

        $lama = $order->tracking_number;
        $order->tracking_number = $resiBaru;
        $order->save();

        // Singgahan pelacakan memakai nomor resi sebagai kunci.
        if (filled($lama)) {
            Cache::forget('lacak-pembeli:' . $lama);
        }
    }

    /**
     * Ongkir disesuaikan Biteship karena berat sebenarnya berbeda dari perkiraan.
     *
     * HANYA DICATAT, tidak mengubah tagihan pembeli. Pembeli sudah membayar dan
     * tidak boleh ditagih ulang karena selisih berat; yang berubah adalah biaya
     * yang ditanggung toko, dan itu perlu terlihat di penghasilan bersih.
     */
    private function catatOngkirBaru(Order $order, Request $request): void
    {
        $ongkirBaru = (int) round((float) ($request->input('shippment_fee')
            ?? $request->input('price') ?? 0));

        if ($ongkirBaru <= 0 || $ongkirBaru === (int) round((float) $order->shipping_actual_cost)) {
            return;
        }

        $selisih = $ongkirBaru - (int) round((float) $order->shipping_actual_cost);

        Log::warning('Ongkir disesuaikan Biteship', [
            'pesanan'    => $order->order_number,
            'sebelumnya' => (int) round((float) $order->shipping_actual_cost),
            'menjadi'    => $ongkirBaru,
            'selisih'    => $selisih,
        ]);

        $order->shipping_actual_cost = $ongkirBaru;

        // Markup ikut dihitung ulang; bisa menjadi nol bila ongkir sebenarnya
        // ternyata melampaui yang ditagihkan ke pembeli.
        $order->shipping_markup_profit = max(
            0,
            (int) round((float) $order->shipping_cost) - $ongkirBaru
        );

        $order->save();
    }

    /**
     * Menerjemahkan status Biteship menjadi status pesanan.
     *
     * Sengaja hemat: hanya "sudah sampai" yang mengubah status pesanan.
     * Perjalanan yang gagal tidak diubah otomatis karena penanganannya
     * bergantung keadaan — dikembalikan, dikirim ulang, atau diganti —
     * dan itu keputusan admin, bukan keputusan sistem.
     */
    private function perbaruiStatus(Order $order, string $status): void
    {
        if (in_array($status, self::GAGAL, true)) {
            Log::warning('Pengiriman bermasalah menurut Biteship', [
                'pesanan' => $order->order_number,
                'status'  => $status,
            ]);

            return;
        }

        if (! in_array($status, self::SAMPAI, true)) {
            return;
        }

        /*
         * Pesanan yang sudah selesai atau dibatalkan tidak diutak-atik lagi.
         * Notifikasi Biteship bisa datang terlambat atau dikirim ulang, dan
         * status yang sudah final tidak boleh mundur karenanya.
         */
        if (in_array($order->status, ['completed', 'cancelled'], true)) {
            return;
        }

        // Dicatat sekali saja; notifikasi ulang tidak menggeser waktunya.
        if ($order->delivered_at !== null) {
            return;
        }

        /*
         * Yang dicatat hanya WAKTU sampainya. Status pesanan sengaja tidak
         * diubah, karena dua alasan:
         *
         *   1. "delivered" bukan status yang sah di sistem ini — daftarnya
         *      hanya pending, processing, shipped, completed, cancelled.
         *      Menuliskannya akan merusak penyaring dan label di semua halaman.
         *
         *   2. Menandai selesai otomatis melewati konfirmasi pembeli, padahal
         *      tenggat pengajuan pengembalian dihitung dari konfirmasi itu.
         *      Pembeli akan kehilangan sebagian masa pengembaliannya tanpa
         *      pernah menyentuh apa pun.
         *
         * Pesanan tetap berstatus "shipped" sampai pembeli menekan konfirmasi.
         */
        $order->delivered_at = now();
        $order->save();

        Log::info('Paket dinyatakan sampai oleh kurir', [
            'pesanan' => $order->order_number,
        ]);
    }

    private function bersihkanSinggahan(Order $order): void
    {
        if (filled($order->tracking_number)) {
            Cache::forget('lacak-pembeli:' . $order->tracking_number);
        }
    }
}
