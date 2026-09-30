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
        'BC'  => 'BCA Virtual Account',
        'M2'  => 'Mandiri Virtual Account',
        'VA'  => 'Maybank Virtual Account',
        'I1'  => 'BNI Virtual Account',
        'B1'  => 'CIMB Niaga Virtual Account',
        'BT'  => 'Permata Virtual Account',
        'A1'  => 'ATM Bersama',
        'AG'  => 'BRI Virtual Account',
        'NC'  => 'BNC (Neo Commerce) Virtual Account',
        'SA'  => 'BSI Virtual Account',
        'QR'  => 'QRIS (Semua E-Wallet)',
        'FT'  => 'Alfamart / Alfamidi',
        'IR'  => 'Indomaret',
    ];

    public function __construct()
    {
        $this->merchantCode    = config('duitku.merchant_code', env('DUITKU_MERCHANT_CODE', 'D0001'));
        $this->apiKey          = config('duitku.api_key', env('DUITKU_API_KEY', '732B39FC61796845775D2C4FB05332AF'));
        $this->isSandbox       = config('duitku.sandbox', env('DUITKU_SANDBOX', true));
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

        $payload = [
            'paymentAmount'     => $amount,
            'paymentMethod'     => $params['payment_method'],
            'merchantOrderId'   => $merchantOrderId,
            'productDetails'    => mb_substr($params['product_details'] ?? 'Pembelian Sepatu RECORD', 0, 250),
            'additionalParam'   => '',
            'merchantUserInfo'  => mb_substr($params['customer_email'] ?? '', 0, 50),
            'customerVaName'    => mb_substr($params['customer_name'] ?? 'Customer', 0, 20),
            'email'             => $params['customer_email'] ?? 'customer@record.test',
            'phoneNumber'       => $params['customer_phone'] ?? '081234567890',
            'callbackUrl'       => $this->callbackBaseUrl . '/duitku/callback',
            'returnUrl'         => $this->callbackBaseUrl . '/pesanan',
            'expiryPeriod'      => $expiredMinutes,
            'itemDetails'       => $this->buildItemDetails($params['items'] ?? [], $amount),
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
     */
    public function verifyCallbackSignature(array $callbackData): bool
    {
        $merchantCode    = $callbackData['merchantCode'] ?? '';
        $amount          = $callbackData['amount'] ?? '';
        $merchantOrderId = $callbackData['merchantOrderId'] ?? '';
        $reference       = $callbackData['reference'] ?? '';
        $resultCode      = $callbackData['resultCode'] ?? '';
        $received        = $callbackData['signature'] ?? '';

        $apiKeyMd5 = md5($this->apiKey);
        $expected  = md5($merchantCode . $amount . $merchantOrderId . $reference . $apiKeyMd5 . $resultCode);

        return hash_equals($expected, $received);
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

    private function buildItemDetails(array $items, int $totalAmount): array
    {
        if (empty($items)) {
            return [[
                'name'     => 'Pembelian Produk RECORD',
                'price'    => $totalAmount,
                'quantity' => 1,
            ]];
        }

        return collect($items)->map(fn ($item) => [
            'name'     => mb_substr($item['product_name'] ?? 'Produk', 0, 50),
            'price'    => (int) ($item['price'] ?? 0),
            'quantity' => (int) ($item['quantity'] ?? 1),
        ])->values()->toArray();
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