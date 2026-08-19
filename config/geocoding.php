<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Penyedia Pencarian Koordinat
    |--------------------------------------------------------------------------
    |
    | "osm"    — OpenStreetMap/Nominatim. Gratis, tanpa akun, tetapi data
    |            nomor rumah di Indonesia tidak lengkap sehingga titik lokasi
    |            berhenti di tingkat jalan atau kecamatan.
    |
    | "google" — Google Maps Geocoding API. Akurat sampai nomor rumah, tetapi
    |            memerlukan akun Google Cloud beserta kunci API dan penagihan
    |            aktif. Isi GOOGLE_MAPS_API_KEY pada berkas .env, lalu ubah
    |            GEOCODING_DRIVER menjadi "google".
    |
    */

    'driver' => env('GEOCODING_DRIVER', 'osm'),

    'google' => [
        'key'    => env('GOOGLE_MAPS_API_KEY'),
        'region' => 'id',
        'bahasa' => 'id',
    ],

    'osm' => [
        'pangkalan' => 'https://nominatim.openstreetmap.org',
    ],

    /*
    | Nominatim membatasi satu permintaan per detik. Google tidak seketat itu,
    | jadi jedanya boleh jauh lebih rapat.
    */
    'jeda_detik' => [
        'osm'    => 1.1,
        'google' => 0.05,
    ],

    /*
    | Umur cache dalam menit. Alamat jarang berpindah, jadi hasil yang ketemu
    | disimpan lama; yang tidak ketemu disimpan sebentar saja.
    */
    'umur_cache' => [
        'ketemu' => 60 * 24 * 30,
        'kosong' => 60 * 6,
    ],

];
