<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pemetaan Metode Pembayaran ke Saluran Midtrans
    |--------------------------------------------------------------------------
    |
    | Tanpa daftar ini, Snap menampilkan SELURUH saluran yang aktif di akun
    | Midtrans. Akibatnya pembeli bisa memilih QRIS di checkout lalu membayar
    | lewat Virtual Account di jendela Snap — dan catatan di panel admin jadi
    | berbeda dengan cara bayar yang sebenarnya.
    |
    | Kunci di sini adalah nilai yang tersimpan pada orders.payment_method.
    |
    */

    'diizinkan' => [
        'QRIS'      => ['other_qris', 'gopay'],
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
    | benar-benar dipakai. Jaring pengaman: seandainya pembeli tetap berhasil
    | memilih saluran lain, catatan toko tetap mengikuti kenyataan.
    |
    */

    // payment_type sederhana yang langsung menentukan metodenya
    'dari_jenis' => [
        'qris'     => 'QRIS',
        'gopay'    => 'QRIS',
        'echannel' => 'Mandiri',
    ],

    // payment_type "bank_transfer" — banknya dibaca dari va_numbers
    'dari_bank' => [
        'bca'     => 'BCA',
        'bni'     => 'BNI',
        'bri'     => 'BRI',
        'permata' => 'Permata',
        'cimb'    => 'CIMB Niaga',
    ],

    // payment_type "cstore" — gerainya dibaca dari kolom store
    'dari_gerai' => [
        'indomaret' => 'Indomaret',
        'alfamart'  => 'Alfamart',
    ],

];
