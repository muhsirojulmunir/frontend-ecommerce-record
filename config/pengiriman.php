<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Titik Asal Pengiriman
    |--------------------------------------------------------------------------
    */

    'toko' => [
        'lintang' => (float) env('STORE_LATITUDE', -7.2275),
        'bujur'   => (float) env('STORE_LONGITUDE', 112.7865),
        'kota'    => env('STORE_CITY', 'Surabaya'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pengiriman Instan
    |--------------------------------------------------------------------------
    |
    | Kurir instan (GoSend, GrabExpress, Lalamove) mengantar dalam hitungan jam
    | memakai motor, sehingga hanya masuk akal untuk tujuan yang dekat dengan
    | toko. Pilihan ini disembunyikan begitu jarak tujuan melewati radius di
    | bawah â€” pembeli luar kota tidak akan melihatnya sama sekali.
    |
    | Radius dihitung sebagai garis lurus dari titik toko. Nilai bawaan 15 km
    | kira-kira sepadan dengan batas Kota Surabaya â€” sesuai permintaan bahwa
    | layanan ini khusus Surabaya. Ancar-ancarnya:
    |
    |   15 km â€” Surabaya dan sekitarnya yang menempel (mis. Waru)
    |   25 km â€” ikut menjangkau Gresik dan Sidoarjo kota
    |   40 km â€” hampir seluruh Gerbangkertosusila
    |
    | Perlu diketahui: jarak garis lurus selalu lebih pendek daripada jarak
    | tempuh sebenarnya, jadi radius 15 km kira-kira setara 18-22 km di jalan.
    |
    */

    'instan' => [
        'aktif'     => (bool) env('SHIPPING_INSTANT_ENABLED', true),
        'radius_km' => (float) env('SHIPPING_INSTANT_RADIUS_KM', 15),

        // Kode kurir instan di Biteship.
        'kurir' => ['gojek', 'grab', 'lalamove'],

        /*
         * Jam layanan instan, mengikuti ketentuan Biteship.
         *
         * Biteship tidak memasang batas keras untuk instan, tetapi menyarankan
         * pemesanan sebelum pukul 16.00 supaya masih ada driver yang bisa
         * mengambil. Di luar jam itu pesanannya kerap tidak mendapat driver
         * dan berakhir gagal.
         *
         * Karena itu pilihan instan disembunyikan sejak halaman checkout, bukan
         * ditampilkan lalu ditolak saat diproses. Pembeli yang sudah membayar
         * ongkos instan lalu paketnya berangkat reguler jauh lebih kecewa
         * daripada pembeli yang sejak awal tidak melihat pilihan itu.
         *
         * PENTING: jam ini WIB. Zona waktu aplikasi diatur UTC, jadi
         * perbandingannya harus dikonversi dulu â€” lihat bolehInstan() di
         * ShippingCostService. Selisihnya 7 jam; tanpa konversi, instan akan
         * tampil dan tersembunyi pada waktu yang sama sekali salah.
         */
        'jam_mulai'   => (int) env('SHIPPING_INSTANT_START_HOUR', 8),
        'jam_selesai' => (int) env('SHIPPING_INSTANT_END_HOUR', 16),
        'zona_waktu'  => env('SHIPPING_TIMEZONE', 'Asia/Jakarta'),

        /*
        | Tarif cadangan, dipakai hanya bila Biteship tidak bisa dihubungi.
        | Perhitungannya: biaya dasar untuk jarak awal, lalu tambahan per km.
        | Angkanya mengikuti tarif publik layanan instan di Surabaya.
        */
        'cadangan' => [
            [
                'kode'       => 'gojek',
                'nama'       => 'GoSend Instant',
                'dasar'      => 12000,   // sampai jarak_dasar_km pertama
                'per_km'     => 2500,
                'jarak_dasar_km' => 3,
                'etd'        => '1-3 jam',
            ],
            [
                'kode'       => 'grab',
                'nama'       => 'GrabExpress Instant',
                'dasar'      => 13000,
                'per_km'     => 2400,
                'jarak_dasar_km' => 3,
                'etd'        => '1-3 jam',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Kurir Reguler
    |--------------------------------------------------------------------------
    */

    'kurir_reguler' => ['jne', 'jnt', 'sicepat', 'anteraja', 'pos'],

    /*
    | Berat rata-rata satu pasang sepatu, dalam gram.
    */
    'berat_per_pasang_gram' => 800,

];
