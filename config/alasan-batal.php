<?php

/*
|--------------------------------------------------------------------------
| Pilihan Alasan Pembatalan Pesanan
|--------------------------------------------------------------------------
|
| Daftar alasan yang bisa dipilih pembeli saat membatalkan pesanan.
| Kunci disimpan ke database, nilainya yang ditampilkan ke pembeli.
|
| Pilihan 'lainnya' selalu ditempatkan paling bawah dan memunculkan
| kolom isian bebas agar pembeli bisa menjelaskan sendiri.
|
*/

return [

    'pilihan' => [
        'berubah_pikiran'   => 'Saya berubah pikiran',
        'salah_ukuran'      => 'Salah pilih ukuran atau warna',
        'salah_alamat'      => 'Alamat pengiriman salah',
        'ongkir_mahal'      => 'Ongkos kirim terlalu mahal',
        'ketemu_lebih_murah' => 'Menemukan harga lebih murah di tempat lain',
        'pesan_ganda'       => 'Tidak sengaja memesan dua kali',
        'terlalu_lama'      => 'Prosesnya terlalu lama',
        'kendala_bayar'     => 'Ada kendala saat pembayaran',
        'lainnya'           => 'Alasan lain',
    ],

    // Kunci yang mewajibkan pembeli mengisi penjelasan tambahan
    'wajib_penjelasan' => 'lainnya',

];
