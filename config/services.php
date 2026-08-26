<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'biteship' => [
        /*
         * Kata sandi bersama untuk webhook pengiriman.
         *
         * Biteship belum menyediakan tanda tangan digital untuk notifikasinya,
         * jadi keasliannya dijaga dengan Custom Header yang kita tentukan
         * sendiri saat mendaftarkan webhook di dasbor mereka. Nilai di sini
         * harus sama persis dengan yang diisikan di sana.
         *
         * Kalau kosong, seluruh notifikasi DITOLAK — bukan diterima begitu
         * saja. Pintu yang dibiarkan terbuka karena kuncinya belum dipasang
         * adalah cara paling umum sebuah webhook disalahgunakan.
         */
        'webhook_token' => env('BITESHIP_WEBHOOK_TOKEN', ''),
    ],

];
