<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;

class MidtransService
{
    protected string $serverKey;
    protected string $clientKey;
    protected bool   $isProduction;
    protected string $snapUrl;
    protected string $apiUrl;

    public function __construct()
    {
        $this->serverKey    = env('MIDTRANS_SERVER_KEY', '');
        $this->clientKey    = env('MIDTRANS_CLIENT_KEY', '');
        $this->isProduction = env('MIDTRANS_IS_PRODUCTION', false);

        $this->snapUrl = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $this->apiUrl = $this->isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function isProduction(): bool
    {
        return $this->isProduction;
    }

    /**
     * Buat Snap Token untuk Midtrans popup payment.
     * Mengembalikan snap_token yang digunakan di frontend.
     */
        public function createSnapToken(Order $order): array
    {
        $order->loadMissing(['user', 'items.product', 'items.productVariant']);

        // Build item details untuk Midtrans
        $itemDetails = [];
        foreach ($order->items as $item) {
            $itemPrice = (int) round($item->price);
            $itemQty   = max(1, (int) $item->quantity);
            $itemName  = mb_substr($item->product_name . ($item->variant_info ? ' - ' . $item->variant_info : ''), 0, 50);

            $itemDetails[] = [
                'id'       => 'PROD-' . $item->product_id . ($item->product_variant_id ? '-' . $item->product_variant_id : ''),
                'price'    => $itemPrice,
                'quantity' => $itemQty,
                'name'     => $itemName ?: 'Produk Record',
            ];
        }

        // Tambah ongkos kirim sebagai item
        if ($order->shipping_cost > 0) {
            $itemDetails[] = [
                'id'       => 'SHIPPING',
                'price'    => (int) round($order->shipping_cost),
                'quantity' => 1,
                'name'     => 'Ongkos Kirim (' . ($order->courier ?: 'Kurir') . ')',
            ];
        }

        // Jaringan Pengaman: Pastikan jumlah dari item_details SAMA PERSIS dengan grand_total
        $totalItemsCost = 0;
        foreach ($itemDetails as $detail) {
            $totalItemsCost += ((int) $detail['price'] * (int) $detail['quantity']);
        }
        $grossAmount = (int) round($order->grand_total);
        $diff = $grossAmount - $totalItemsCost;

        if ($diff !== 0) {
            $itemDetails[] = [
                'id'       => 'ADJUSTMENT',
                'price'    => $diff,
                'quantity' => 1,
                'name'     => $diff < 0 ? 'Potongan Diskon / Promo' : 'Penyesuaian Total',
            ];
        }

        $addr = is_array($order->shipping_address)
            ? $order->shipping_address
            : (json_decode((string) $order->shipping_address, true) ?: []);

        $customerName  = $order->user?->name ?? ($addr['recipient_name'] ?? 'Pelanggan Record');
        $customerEmail = $order->user?->email ?? 'customer@recordshoes.com';
        $customerPhone = $addr['phone'] ?? ($order->user?->phone ?? '08123456789');

        $params = [
            'transaction_details' => [
                'order_id'     => $order->order_number,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $customerName,
                'email'      => $customerEmail,
                'phone'      => $customerPhone,
                'shipping_address' => [
                    'first_name'   => $addr['recipient_name'] ?? $customerName,
                    'phone'        => $customerPhone,
                    'address'      => $addr['address_line'] ?? ($addr['address'] ?? 'Alamat Pengiriman'),
                    'city'         => $addr['city'] ?? '',
                    'postal_code'  => $addr['postal_code'] ?? '',
                    'country_code' => 'IDN',
                ],
            ],
            'item_details' => $itemDetails,
            'callbacks' => [
                'finish' => route('checkout.payment.finish', $order->order_number),
            ],
        ];

        $kanal = config('midtrans-kanal.diizinkan.' . $order->payment_method);
        if (! empty($kanal)) {
            $params['enabled_payments'] = $kanal;
        }

        try {
            $response = $this->httpPost($this->snapUrl, $params);

            if (isset($response['token'])) {
                return [
                    'success'    => true,
                    'token'      => $response['token'],
                    'redirect'   => $response['redirect_url'] ?? null,
                    'client_key' => $this->clientKey,
                    'snap_url'   => $this->isProduction
                        ? 'https://app.midtrans.com/snap/snap.js'
                        : 'https://app.sandbox.midtrans.com/snap/snap.js',
                ];
            }

            \Illuminate\Support\Facades\Log::error('Midtrans createSnapToken failed', ['order' => $order->order_number, 'response' => $response]);
            return ['success' => false, 'message' => 'Gagal mendapatkan token Midtrans.', 'raw' => $response];

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Midtrans createSnapToken Exception: ' . $e->getMessage(), ['order' => $order->order_number]);

            // Percobaan kedua: jika error karena enabled_payments atau order_id duplikat di Sandbox
            try {
                // Hapus batasan enabled_payments dan tambahkan timestamp unik pada order_id jika perlu
                unset($params['enabled_payments']);
                $params['transaction_details']['order_id'] = $order->order_number . '-' . time();

                $response = $this->httpPost($this->snapUrl, $params);

                if (isset($response['token'])) {
                    return [
                        'success'    => true,
                        'token'      => $response['token'],
                        'redirect'   => $response['redirect_url'] ?? null,
                        'client_key' => $this->clientKey,
                        'snap_url'   => $this->isProduction
                            ? 'https://app.midtrans.com/snap/snap.js'
                            : 'https://app.sandbox.midtrans.com/snap/snap.js',
                    ];
                }
            } catch (\Exception $ex) {
                \Illuminate\Support\Facades\Log::error('Midtrans retry SnapToken Exception: ' . $ex->getMessage());
            }

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Verifikasi notifikasi callback dari Midtrans.
     * Return array berisi status transaksi yang sudah diverifikasi.
     */
    public function verifyCallback(Request $request): array
    {
        $notification = $request->all();

        $orderId           = $notification['order_id']           ?? '';
        $statusCode        = $notification['status_code']        ?? '';
        $grossAmount       = $notification['gross_amount']       ?? '';
        $serverKey         = $this->serverKey;
        $signatureKey      = $notification['signature_key']      ?? '';

        // Verifikasi signature.
        // Dibandingkan dengan hash_equals, bukan !==, supaya lamanya
        // perbandingan tidak bergantung pada seberapa banyak karakter awal
        // yang sudah benar — perbandingan biasa membocorkan petunjuk itu.
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if (! hash_equals($expectedSignature, (string) $signatureKey)) {
            return ['valid' => false, 'message' => 'Invalid signature'];
        }

        $transactionStatus = $notification['transaction_status'] ?? '';
        $fraudStatus       = $notification['fraud_status']       ?? 'accept';
        $paymentType       = $notification['payment_type']       ?? '';

        // Tentukan status pembayaran dari Midtrans
        $paymentStatus = 'unpaid';
        $orderStatus   = null;

        if ($transactionStatus === 'capture') {
            $paymentStatus = ($fraudStatus === 'accept') ? 'paid' : 'failed';
            $orderStatus   = ($fraudStatus === 'accept') ? 'processing' : null;
        } elseif ($transactionStatus === 'settlement') {
            $paymentStatus = 'paid';
            $orderStatus   = 'processing';
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $paymentStatus = 'failed';
        } elseif ($transactionStatus === 'refund') {
            $paymentStatus = 'refunded';
            $orderStatus   = 'cancelled';
        }

        return [
            'valid'              => true,
            'order_number'       => $orderId,
            'payment_status'     => $paymentStatus,
            'order_status'       => $orderStatus,
            'transaction_status' => $transactionStatus,
            'payment_type'       => $paymentType,
            'metode'             => $this->metodeDariNotifikasi($notification),

            // Nominal yang dinyatakan Midtrans. Ikut ditandatangani, jadi tidak
            // bisa dipalsukan — dipakai pemanggil untuk mencocokkan dengan
            // tagihan pesanan sebelum menandainya lunas.
            'gross_amount'       => (float) $grossAmount,
        ];
    }

    /**
 * Menerjemahkan notifikasi Midtrans menjadi nama metode pembayaran seperti yang dipakai toko (BCA, ...
 *
 * @return string|null null bila salurannya tidak dikenali; catatan lama
 */
    public function metodeDariNotifikasi(array $notification): ?string
    {
        $jenis = strtolower((string) ($notification['payment_type'] ?? ''));

        if ($langsung = config('midtrans-kanal.dari_jenis.' . $jenis)) {
            return $langsung;
        }

        if ($jenis === 'bank_transfer') {
            // Permata dikirim Midtrans lewat kolomnya sendiri, bukan va_numbers.
            $bank = $notification['va_numbers'][0]['bank']
                ?? (filled($notification['permata_va_number'] ?? null) ? 'permata' : '');

            return config('midtrans-kanal.dari_bank.' . strtolower((string) $bank));
        }

        if ($jenis === 'cstore') {
            $gerai = strtolower((string) ($notification['store'] ?? ''));

            return config('midtrans-kanal.dari_gerai.' . $gerai);
        }

        return null;
    }

    /**
     * Cek status transaksi langsung ke Midtrans API.
     */
    public function checkStatus(string $orderNumber): array
    {
        try {
            $url = $this->apiUrl . '/' . $orderNumber . '/status';
            return $this->httpGet($url);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // ─── HTTP Helpers ──────────────────────────────────────────────────────────

    protected function httpPost(string $url, array $body): array
    {
        $auth = base64_encode($this->serverKey . ':');

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Basic ' . $auth,
            ],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $result   = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($result, true) ?? [];

        if ($httpCode >= 400) {
            throw new \Exception('Midtrans API error ' . $httpCode . ': ' . $result);
        }

        return $decoded;
    }

    protected function httpGet(string $url): array
    {
        $auth = base64_encode($this->serverKey . ':');

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Authorization: Basic ' . $auth,
            ],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true) ?? [];
    }
}
