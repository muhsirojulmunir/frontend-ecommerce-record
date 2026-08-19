<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\CatatAktivitas;
use App\Services\PembatalanPesananService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Tampilkan daftar semua pesanan milik customer yang sedang login.
     */
    public function index()
    {
        // "items.review" ikut dimuat supaya tanda "Belum Dinilai" bisa dihitung
        // tanpa menembak satu kueri per barang di setiap kartu pesanan.
        $orders = Auth::user()->orders()
            ->with(['items.product', 'items.review'])
            ->latest()
            ->paginate(10);
        return view('orders.index', compact('orders'));
    }

    /**
     * Halaman lacak pesanan: menampilkan pesanan milik customer yang sedang
     * login beserta nomor resinya. Hanya bisa diakses setelah masuk, sebab
     * nomor resi termasuk data pribadi pemesan.
     */
    public function tracking()
    {
        $orders = Auth::user()->orders()
            ->with('items')
            ->latest()
            ->get();

        // Pesanan yang sudah punya resi ditempatkan lebih dulu agar mudah dicari
        $adaResi   = $orders->filter(fn ($o) => filled($o->tracking_number));
        $belumResi = $orders->reject(fn ($o) => filled($o->tracking_number));

        return view('orders.tracking', [
            'adaResi'   => $adaResi,
            'belumResi' => $belumResi,
            'total'     => $orders->count(),
        ]);
    }

    /**
     * Tampilkan detail satu pesanan berdasarkan nomor pesanan (dengan timeline tracking).
     */
    public function show($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            // "items.review" ikut dimuat di sini supaya halaman tidak
            // menembak satu kueri per barang hanya untuk tahu mana yang
            // sudah dinilai.
            ->with(['items.product', 'items.productVariant', 'items.review'])
            ->firstOrFail();

        $order->load('returns');

        /*
         * Barang yang masih menunggu dinilai.
         *
         * Hanya berlaku untuk pesanan yang sudah selesai — sebelum barangnya
         * diterima, pembeli belum punya dasar apa pun untuk menilai.
         */
        $belumDinilai = $order->status === 'completed'
            ? $order->items->filter(fn ($item) => $item->review === null)
            : collect();

        /*
         * Kode referal ditampilkan pada pesanan yang sudah selesai.
         *
         * Keabsahannya dihitung ulang di sini, bukan sekadar melihat apakah
         * kolomnya terisi — kode yang pesanannya dibatalkan harus terlihat
         * hangus oleh pemiliknya sendiri, bukan hanya oleh orang lain yang
         * mencoba memakainya.
         */
        $referral = app(\App\Services\ReferralService::class);
        $pemilik  = Auth::user();

        $kodeReferal  = $pemilik->referral_code;
        $referalAktif = filled($kodeReferal) && $referral->punyaPesananSah($pemilik);

        return view('orders.show', compact('order', 'kodeReferal', 'referalAktif', 'belumDinilai'));
    }

    /**
     * AJAX: Ambil riwayat tracking real-time dari Biteship API.
     * Dipanggil via fetch() di halaman detail pesanan (Shopee-style inline tracking).
     */
    public function trackingStatus(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$order->tracking_number) {
            return response()->json(['success' => false, 'message' => 'Nomor resi belum tersedia.']);
        }

        $apiKey = env('BITESHIP_API_KEY');
        if (!$apiKey) {
            return response()->json(['success' => false, 'message' => 'Layanan tracking belum dikonfigurasi.']);
        }

        $isProduction = env('BITESHIP_IS_PRODUCTION', false);
        $baseUrl = $isProduction
            ? 'https://api.biteship.com/v1'
            : 'https://api.biteship.com/v1'; // Biteship tracking pakai URL yang sama di sandbox

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->get($baseUrl . '/trackings/' . $order->tracking_number);

            $data = $response->json();

            if (!$response->successful() || empty($data['success'])) {
                // Fallback: tampilkan info statis berdasarkan status pesanan
                return response()->json([
                    'success'         => true,
                    'fallback'        => true,
                    'tracking_number' => $order->tracking_number,
                    'courier'         => $order->courier,
                    'status'          => $order->status,
                    'history'         => $this->generateFallbackHistory($order),
                ]);
            }

            // Normalisasi history dari Biteship
            $history = collect($data['history'] ?? [])
                ->map(fn ($h) => [
                    'time'        => $h['updated_time'] ?? $h['timestamp'] ?? null,
                    'status'      => $h['status'] ?? '',
                    'description' => $h['note'] ?? $h['description'] ?? '',
                    'location'    => $h['location'] ?? '',
                ])
                ->values()
                ->toArray();

            return response()->json([
                'success'         => true,
                'tracking_number' => $order->tracking_number,
                'courier'         => $order->courier,
                'status'          => $data['courier']['status'] ?? $order->status,
                'status_label'    => $data['courier']['status_description'] ?? null,
                'history'         => $history,
                'origin'          => $data['origin'] ?? [],
                'destination'     => $data['destination'] ?? [],
            ]);

        } catch (\Throwable $e) {
            Log::warning('Biteship tracking error: ' . $e->getMessage());

            return response()->json([
                'success'         => true,
                'fallback'        => true,
                'tracking_number' => $order->tracking_number,
                'courier'         => $order->courier,
                'status'          => $order->status,
                'history'         => $this->generateFallbackHistory($order),
            ]);
        }
    }

    /**
     * Riwayat tracking statis berdasarkan status pesanan (fallback jika Biteship API tidak tersedia).
     */
    private function generateFallbackHistory(Order $order): array
    {
        $history = [];

        if ($order->created_at) {
            $history[] = ['time' => $order->created_at->toIso8601String(), 'status' => 'order_placed', 'description' => 'Pesanan berhasil dibuat.', 'location' => 'Toko RECORD'];
        }
        if ($order->payment_status === 'paid') {
            $history[] = ['time' => ($order->updated_at ?? $order->created_at)->toIso8601String(), 'status' => 'payment_confirmed', 'description' => 'Pembayaran telah dikonfirmasi.', 'location' => 'Toko RECORD'];
        }
        if (in_array($order->status, ['processing', 'shipped', 'completed'])) {
            $history[] = ['time' => ($order->updated_at ?? $order->created_at)->toIso8601String(), 'status' => 'processing', 'description' => 'Pesanan sedang dikemas oleh penjual.', 'location' => 'Surabaya'];
        }
        if (in_array($order->status, ['shipped', 'completed'])) {
            $history[] = ['time' => ($order->updated_at ?? $order->created_at)->toIso8601String(), 'status' => 'on_delivery', 'description' => 'Paket diserahkan ke kurir ' . $order->courier . ' dan sedang dalam perjalanan.', 'location' => 'Surabaya'];
        }
        if ($order->status === 'completed') {
            $addr = $order->shipping_address;
            $history[] = ['time' => ($order->completed_at ?? $order->updated_at)->toIso8601String(), 'status' => 'delivered', 'description' => 'Paket telah diterima oleh penerima.', 'location' => ($addr['city'] ?? 'Tujuan')];
        }

        return array_reverse($history); // Terbaru di atas
    }

    /**
     * Invoice pesanan. Hanya terbit setelah pembayaran lunas, sebab invoice
     * merupakan bukti pembayaran — bukan sekadar rincian pesanan.
     */
    public function invoice($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->with(['items.product', 'items.productVariant', 'user'])
            ->firstOrFail();

        if (blank($order->invoice_number)) {
            return redirect()
                ->route('orders.show', $order->order_number)
                ->with('error', 'Invoice baru terbit setelah pembayaran kami terima.');
        }

        return view('orders.invoice', compact('order'));
    }

    /**
     * Konfirmasi pesanan telah diterima customer (shipped -> completed).
     */
    public function confirm($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (in_array($order->status, ['shipped', 'processing'])) {
            $order->status = 'completed';
            // Dicatat supaya batas waktu pengajuan pengembalian punya
            // patokan yang tidak ikut bergeser saat baris pesanan disentuh.
            $order->completed_at = now();
            $order->save();

            return redirect()->back()->with('success', 'Terima kasih! Pesanan Anda telah dikonfirmasi selesai.');
        }

        return redirect()->back()->with('error', 'Pesanan belum dapat dikonfirmasi.');
    }

    /**
     * Batalkan pesanan milik customer jika masih dalam status pending & unpaid.
     * Pembeli wajib memilih alasan pembatalan agar toko bisa menindaklanjutinya.
     */
    public function cancel(Request $request, $orderNumber)
    {
        $pilihan      = config('alasan-batal.pilihan', []);
        $wajibJelas   = config('alasan-batal.wajib_penjelasan');

        $data = $request->validate([
            'alasan'      => ['required', Rule::in(array_keys($pilihan))],
            'penjelasan'  => [
                // Penjelasan hanya wajib bila pembeli memilih "Alasan lain"
                Rule::requiredIf(fn () => $request->input('alasan') === $wajibJelas),
                'nullable', 'string', 'max:500',
            ],
        ], [
            'alasan.required'     => 'Silakan pilih dulu alasan pembatalannya.',
            'alasan.in'           => 'Alasan yang dipilih tidak dikenali.',
            'penjelasan.required' => 'Mohon jelaskan sedikit alasan pembatalanmu.',
            'penjelasan.max'      => 'Penjelasan maksimal 500 karakter.',
        ]);

        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        /*
         * Jalurnya ditentukan oleh sudah atau belumnya pengiriman diatur —
         * bukan oleh sudah atau belumnya dibayar. Lihat alasannya di
         * App\Services\PembatalanPesananService.
         */
        $pembatalan = app(PembatalanPesananService::class);
        $alasan     = $pilihan[$data['alasan']];
        $penjelasan = $data['penjelasan'] ?? null;

        switch ($pembatalan->jalur($order)) {
            case PembatalanPesananService::LANGSUNG_TANPA_DANA:
                $pembatalan->batalkan($order, $alasan, $penjelasan);

                return redirect()->route('orders.index')
                    ->with('success', 'Pesanan berhasil dibatalkan. '
                        . 'Terima kasih sudah memberi tahu alasannya.');

            case PembatalanPesananService::LANGSUNG_REFUND:
                $nominal = (float) $order->grand_total;
                $pembatalan->batalkan($order, $alasan, $penjelasan);

                return redirect()->route('orders.index')
                    ->with('success', 'Pesanan berhasil dibatalkan. Dana sebesar Rp '
                        . number_format($nominal, 0, ',', '.')
                        . ' sudah kami kembalikan ke saldo R_Pay-mu.');

            case PembatalanPesananService::LEWAT_PENGEMBALIAN:
                return back()->with('error',
                    'Barangmu sudah diserahkan ke kurir, jadi pesanannya tidak bisa dibatalkan lagi. '
                    . 'Silakan ajukan pengembalian setelah paketnya sampai — dananya tetap kami '
                    . 'kembalikan penuh bila pengajuanmu disetujui.');

            default:
                return back()->with('error', $order->status === 'completed'
                    ? 'Pesanan yang sudah selesai tidak bisa dibatalkan. '
                        . 'Kalau ada masalah dengan barangnya, ajukan pengembalian.'
                    : 'Pesanan ini sudah dibatalkan sebelumnya.');
        }
    }
}
