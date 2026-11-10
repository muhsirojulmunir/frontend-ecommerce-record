<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

require_once base_path('vendor/duitkupg/duitku-php/Duitku.php');

/**
 * DuitkuService — Integrasi resmi Duitku Payment Gateway (Direct API).
 */
class DuitkuService
{
    protected string $merchantCode;
    protected string $apiKey;
    protected bool $isSandbox;
    protected string $callbackBaseUrl;
    protected \Duitku\Config $config;

    public const PAYMENT_METHODS = [
        'BNI'         => 'BNI Virtual Account',
        'BRI'         => 'BRI Virtual Account',
        'MANDIRI'     => 'Mandiri Virtual Account',
        'BSI'         => 'BSI Virtual Account',
        'PERMATA'     => 'Permata Virtual Account',
        'CIMB'        => 'CIMB Niaga Virtual Account',
        'MAYBANK'     => 'Maybank Virtual Account',
        'ATM_BERSAMA' => 'ATM Bersama',
        'ALFAMART'    => 'Alfamart / Retail',
        'BCA'         => 'BCA Virtual Account',
        'QRIS'        => 'QRIS (Semua E-Wallet)',
        // Kode bawaan Duitku
        'BC'          => 'BCA Virtual Account',
        'M2'          => 'Mandiri Virtual Account',
        'VA'          => 'Maybank Virtual Account',
        'I1'          => 'BNI Virtual Account',
        'B1'          => 'CIMB Niaga Virtual Account',
        'BT'          => 'Permata Virtual Account',
        'A1'          => 'ATM Bersama',
        'BR'          => 'BRI Virtual Account',
        'AG'          => 'Artha Graha Virtual Account',
        'NC'          => 'BNC (Neo Commerce) Virtual Account',
        'BV'          => 'BSI Virtual Account',
        'SA'          => 'BSI Virtual Account',
        'QR'          => 'QRIS (Semua E-Wallet)',
        'SP'          => 'QRIS ShopeePay',
        'FT'          => 'Alfamart / Alfamidi',
        'IR'          => 'Indomaret',
        'DM'          => 'Danamon Virtual Account',
        'S1'          => 'Sampoerna Virtual Account',
    ];

    /**
     * Konversi kode bank ramah pengguna ke kode internal Duitku.
     * Contoh: 'BNI' -> 'I1', 'BRI' -> 'BR', 'MANDIRI' -> 'M2', 'BSI' -> 'BV'.
     */
    public static function mapToDuitkuMethod(string $method): string
    {
        $map = [
            'BNI'         => 'I1',
            'BRI'         => 'BR',
            'MANDIRI'     => 'M2',
            'BSI'         => 'BV',
            'PERMATA'     => 'BT',
            'CIMB'        => 'B1',
            'MAYBANK'     => 'VA',
            'ATM_BERSAMA' => 'A1',
            'ALFAMART'    => 'FT',
            'BCA'         => 'BC',
            'QRIS'        => 'QR',
        ];

        return $map[strtoupper(trim($method))] ?? $method;
    }

    /**
     * Konversi kode internal Duitku ke kode bersih yang ramah pengguna.
     * Contoh: 'I1' -> 'BNI', 'BR' -> 'BRI', 'M2' -> 'MANDIRI', 'BV' -> 'BSI'.
     */
    public static function mapFromDuitkuMethod(string $method): string
    {
        $map = [
            'I1' => 'BNI',
            'BR' => 'BRI',
            'AG' => 'BRI',
            'M2' => 'MANDIRI',
            'BV' => 'BSI',
            'SA' => 'BSI',
            'BT' => 'PERMATA',
            'B1' => 'CIMB',
            'VA' => 'MAYBANK',
            'A1' => 'ATM_BERSAMA',
            'FT' => 'ALFAMART',
            'IR' => 'INDOMARET',
            'BC' => 'BCA',
            'QR' => 'QRIS',
            'SP' => 'QRIS',
        ];

        return $map[strtoupper(trim($method))] ?? $method;
    }

    public function __construct()
    {
        $this->merchantCode    = config('duitku.merchant_code', env('DUITKU_MERCHANT_CODE', 'D0001'));
        $this->apiKey          = config('duitku.api_key', env('DUITKU_API_KEY', '732B39FC61796845775D2C4FB05332AF'));
        $this->isSandbox       = str_starts_with($this->merchantCode, 'DS') || filter_var(config('duitku.sandbox', env('DUITKU_SANDBOX', true)), FILTER_VALIDATE_BOOLEAN);
        $this->callbackBaseUrl = config('app.url', 'http://127.0.0.1:8000');

        $this->config = new \Duitku\Config($this->apiKey, $this->merchantCode);
        $this->config->setSandboxMode($this->isSandbox);
        $this->config->setDuitkuLogs(false);
    }

    /**
     * Buat transaksi pembayaran baru di Duitku.
     */
    public function createTransaction(array $params): array
    {
        $merchantOrderId = (string) $params['order_number'];
        $amount          = (int) $params['amount'];
        $expiredMinutes  = (int) ($params['expired_minutes'] ?? 1440);

        $productDetails  = (string) ($params['product_details'] ?? ('Pembelian Sepatu RECORD - ' . $merchantOrderId));

        $payload = [
            'paymentAmount'     => $amount,
            'paymentMethod'     => self::mapToDuitkuMethod($params['payment_method']),
            'merchantOrderId'   => $merchantOrderId,
            'productDetails'    => mb_substr($productDetails, 0, 250),
            'additionalParam'   => '',
            'merchantUserInfo'  => mb_substr($params['customer_email'] ?? '', 0, 50),
            'customerVaName'    => mb_substr($params['customer_name'] ?? 'Customer', 0, 20),
            'email'             => $params['customer_email'] ?? 'customer@record.test',
            'phoneNumber'       => $params['customer_phone'] ?? '081234567890',
            'callbackUrl'       => $this->callbackBaseUrl . '/duitku/callback',
            'returnUrl'         => $this->callbackBaseUrl . '/pesanan',
            'expiryPeriod'      => $expiredMinutes,
            'itemDetails'       => $this->buildItemDetails($params['items'] ?? [], $amount, $productDetails),
            'customerDetail'    => [
                'firstName'     => mb_substr($params['customer_name'] ?? 'Customer', 0, 20),
                'lastName'      => '',
                'email'         => $params['customer_email'] ?? 'customer@record.test',
                'phoneNumber'   => $params['customer_phone'] ?? '081234567890',
            ],
        ];

        try {
            Log::info('Duitku createInvoice request', [
                'order'   => $merchantOrderId,
                'amount'  => $amount,
                'method'  => $params['payment_method'],
                'sandbox' => $this->isSandbox,
            ]);

            $rawResponse = \Duitku\Api::createInvoice($payload, $this->config);
            $data        = json_decode($rawResponse, true) ?? [];

            Log::info('Duitku createInvoice response', $data);

            if (($data['statusCode'] ?? '') === '00') {
                return [
                    'success'    => true,
                    'paymentUrl' => $data['paymentUrl'] ?? null,
                    'reference'  => $data['reference'] ?? null,
                    'vaNumber'   => $data['vaNumber'] ?? null,
                    'qrCode'     => $data['qrCode'] ?? ($data['qrString'] ?? null),
                    'message'    => $data['statusMessage'] ?? 'Transaksi Duitku berhasil dibuat.',
                ];
            }

            return $this->fail($data['statusMessage'] ?? 'Gagal membuat tagihan Duitku.');

        } catch (\Throwable $e) {
            // Jika Duitku mengembalikan 409 duplicate request (orderId pernah dikirim),
            // otomatis coba sekali lagi dengan suffix timestamp agar transaksi tetap berhasil
            if (str_contains($e->getMessage(), '409') || str_contains(strtolower($e->getMessage()), 'duplicate')) {
                try {
                    $retryOrderId = $merchantOrderId . '-' . substr((string) time(), -4);
                    $payload['merchantOrderId'] = $retryOrderId;
                    Log::info('Duitku 409 duplicate terdeteksi, mencoba ulang dengan orderId: ' . $retryOrderId);

                    $rawResponse = \Duitku\Api::createInvoice($payload, $this->config);
                    $data        = json_decode($rawResponse, true) ?? [];

                    Log::info('Duitku createInvoice retry response', $data);

                    if (($data['statusCode'] ?? '') === '00') {
                        return [
                            'success'    => true,
                            'paymentUrl' => $data['paymentUrl'] ?? null,
                            'reference'  => $data['reference'] ?? null,
                            'vaNumber'   => $data['vaNumber'] ?? null,
                            'qrCode'     => $data['qrCode'] ?? ($data['qrString'] ?? null),
                            'message'    => $data['statusMessage'] ?? 'Transaksi Duitku berhasil dibuat.',
                        ];
                    }
                } catch (\Throwable $retryErr) {
                    Log::error('Duitku retry invoice exception: ' . $retryErr->getMessage());
                }
            }

            Log::error('Duitku createInvoice exception: ' . $e->getMessage());
            return $this->fail('Koneksi Duitku: ' . $e->getMessage());
        }
    }

    /**
     * Cek status transaksi ke Duitku.
     */
    public function checkTransactionStatus(string $merchantOrderId): array
    {
        try {
            $rawResponse = \Duitku\Api::transactionStatus($merchantOrderId, $this->config);
            $data        = json_decode($rawResponse, true) ?? [];

            return [
                'success'    => true,
                'statusCode' => $data['statusCode'] ?? null,
                'statusMsg'  => $data['statusMessage'] ?? '',
                'reference'  => $data['reference'] ?? null,
                'amount'     => $data['amount'] ?? null,
                'message'    => $data['statusMessage'] ?? 'OK',
            ];
        } catch (\Throwable $e) {
            Log::error('Duitku transactionStatus exception: ' . $e->getMessage());
            return [
                'success'    => false,
                'statusCode' => null,
                'statusMsg'  => $e->getMessage(),
                'message'    => $e->getMessage(),
            ];
        }
    }

    /**
     * Verifikasi signature callback webhook dari Duitku.
     * Mendukung rumus standar Duitku API v1/POP dan Direct API.
     */
    public function verifyCallbackSignature(array $callbackData): bool
    {
        $merchantCode    = (string) ($callbackData['merchantCode'] ?? '');
        $amount          = (string) ($callbackData['amount'] ?? '');
        $merchantOrderId = (string) ($callbackData['merchantOrderId'] ?? '');
        $reference       = (string) ($callbackData['reference'] ?? '');
        $resultCode      = (string) ($callbackData['resultCode'] ?? '');
        $received        = (string) ($callbackData['signature'] ?? '');

        if (empty($received) || empty($this->apiKey)) {
            return false;
        }

        // 1. Rumus standar Duitku: MD5(merchantCode + amount + merchantOrderId + apiKey)
        $standardExpected = md5($merchantCode . $amount . $merchantOrderId . $this->apiKey);
        if (hash_equals($standardExpected, $received)) {
            return true;
        }

        // 2. Rumus alternatif Duitku Direct: MD5(merchantCode + amount + merchantOrderId + reference + MD5(apiKey) + resultCode)
        $apiKeyMd5 = md5($this->apiKey);
        $legacyExpected = md5($merchantCode . $amount . $merchantOrderId . $reference . $apiKeyMd5 . $resultCode);
        if (hash_equals($legacyExpected, $received)) {
            return true;
        }

        return false;
    }

    public function isPaymentSuccessful(?string $resultCode): bool
    {
        return $resultCode === '00';
    }

    public function isPaymentPending(?string $resultCode): bool
    {
        return $resultCode === '01';
    }

    public function isPaymentFailed(?string $resultCode): bool
    {
        return $resultCode === '02';
    }

    /**
     * Susun rincian item pesanan agar presisi totalnya sama persis dengan paymentAmount.
     */
    private function buildItemDetails(array $items, int $totalAmount, string $productDetails = ''): array
    {
        // Untuk menjamin 100% total itemDetails selalu presisi sama dengan paymentAmount
        // (menghindari error 409 'Payment amount must be equal to all item price' akibat diskon/voucher/ongkir/kupon),
        // Duitku mewajibkan: sum(itemDetails[i].price * itemDetails[i].quantity) == paymentAmount.
        $label = !empty($productDetails) ? $productDetails : 'Pembelian Produk RECORD';
        return [
            [
                'name'     => mb_substr($label, 0, 50),
                'price'    => (int) $totalAmount,
                'quantity' => 1,
            ]
        ];
    }

    private function fail(string $message): array
    {
        return [
            'success'    => false,
            'paymentUrl' => null,
            'reference'  => null,
            'vaNumber'   => null,
            'qrCode'     => null,
            'message'    => $message,
        ];
    }
}