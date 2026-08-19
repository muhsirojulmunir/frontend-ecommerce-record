<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kode Referal
    |--------------------------------------------------------------------------
    |
    | Diskon untuk pembeli dan komisi untuk pemilik kode sengaja dibuat sama
    | besar, supaya keduanya sama-sama diuntungkan.
    |
    | Keduanya dihitung dari HARGA BARANG saja (total_price), bukan dari total
    | yang dibayar. Ongkos kirim itu uang kurir, bukan pendapatan toko —
    | memberi komisi darinya berarti toko membayar dari kantong sendiri.
    |
    */

    'awalan' => env('REFERRAL_PREFIX', 'RECORD'),

    'persen_diskon' => 3,
    'persen_komisi' => 3,

    /*
    | Batas panjang bagian nama pada kode, supaya kode tetap mudah diketik
    | ulang oleh orang lain.
    */
    'maks_nama' => 14,

];
