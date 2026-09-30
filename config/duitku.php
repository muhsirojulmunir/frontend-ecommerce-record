<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Duitku Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi Duitku Payment Gateway.
    | Isi DUITKU_MERCHANT_CODE dan DUITKU_API_KEY di file .env.
    |
    | Dokumentasi: https://docs.duitku.com/api/id/
    |
    */

    'merchant_code' => env('DUITKU_MERCHANT_CODE', ''),
    'api_key'       => env('DUITKU_API_KEY', ''),

    /*
    | Sandbox mode: true = gunakan environment testing Duitku
    | Set DUITKU_SANDBOX=false untuk production
    */
    'sandbox' => env('DUITKU_SANDBOX', true),

    /*
    | Waktu kedaluarsa transaksi dalam menit (default 24 jam)
    */
    'expiry_minutes' => env('DUITKU_EXPIRY_MINUTES', 1440),
];
