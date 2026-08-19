<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        /*
         * Berkas bersama dengan panel admin.
         *
         * Toko dan panel admin adalah dua aplikasi terpisah dengan folder
         * storage masing-masing, tetapi berbagi satu basis data. Berkas yang
         * diunggah pembeli di sini harus bisa dibuka admin di sana — kalau
         * ditulis ke storage toko, admin hanya akan melihat tautan mati.
         *
         * Karena itu tujuannya diarahkan ke storage panel admin, mengikuti
         * pola yang sudah dipakai untuk gambar produk (BACKEND_STORAGE_URL).
         * Bila BACKEND_STORAGE_PATH tidak diisi, berkasnya jatuh ke storage
         * sendiri supaya pengunggahan tidak gagal total.
         */
        'bersama' => [
            'driver'     => 'local',
            'root'       => env('BACKEND_STORAGE_PATH') ?: storage_path('app/public'),
            'url'        => rtrim(env('BACKEND_STORAGE_URL', env('APP_URL', 'http://localhost') . '/storage'), '/'),
            'visibility' => 'public',
            'throw'      => false,
            'report'     => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
