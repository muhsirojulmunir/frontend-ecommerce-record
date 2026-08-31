<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pemetaan Metode Pembayaran ke Saluran Midtrans
    |--------------------------------------------------------------------------
    |
    | Kunci di sini adalah nilai yang dipilih pembeli pada checkout.
    | Midtrans Snap akan HANYA menampilkan saluran yang sesuai dengan pilihan
    | pembeli di checkout (BCA -> hanya BCA VA, QRIS -> hanya QRIS, dsb).
    |
    */

    'diizinkan' => [
        'QRIS'      => ['other_qris', 'gopay', 'shopeepay'],
        'BCA'       => ['bca_va'],
        'BNI'       => ['bni_va'],
        'BRI'       => ['bri_va'],
        'Mandiri'   => ['echannel'],
        'Indomaret' => ['indomaret'],
        'Alfamart'  => ['alfamart'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pembacaan Balik dari Notifikasi Midtrans
    |--------------------------------------------------------------------------
    |
    | Dipakai untuk menyelaraskan orders.payment_method dengan cara bayar yang
    | benar-benar dipakai setelah webhook Midtrans masuk.
    |
    */

    'dari_jenis' => [
        'qris'        => 'QRIS',
        'gopay'       => 'QRIS',
        'shopeepay'   => 'QRIS',
        'echannel'    => 'Mandiri',
        'cstore'      => 'Indomaret',
        'credit_card' => 'Kartu Kredit',
    ],

    'dari_bank' => [
        'bca'     => 'BCA',
        'bni'     => 'BNI',
        'bri'     => 'BRI',
        'permata' => 'Permata',
        'cimb'    => 'CIMB Niaga',
        'mandiri' => 'Mandiri',
    ],

    'dari_gerai' => [
        'indomaret' => 'Indomaret',
        'alfamart'  => 'Alfamart',
    ],

];