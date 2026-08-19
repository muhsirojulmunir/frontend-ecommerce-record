<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Penilaian Produk
    |--------------------------------------------------------------------------
    |
    | Bintang wajib, komentar dan foto sepenuhnya opsional. Memaksa pembeli
    | menulis hanya menghasilkan komentar asal-asalan yang tidak menolong
    | pembeli berikutnya; bintangnya sendiri sudah cukup sebagai penilaian.
    |
    | Batas ukuran foto berlaku dua kali: sebagai sasaran pemampatan di
    | peramban, dan sebagai batas terakhir di peladen. Foto yang melebihi
    | batas tidak ditolak, melainkan dikecilkan lebih dulu.
    |
    */

    'maks_foto'    => 3,
    'maks_foto_mb' => 2,

    // Sisi terpanjang foto setelah dimampatkan.
    'maks_sisi_foto' => 1600,

    // Pemampat menyasar sedikit di bawah batas, bukan tepat di batasnya.
    'sasaran_pemampatan' => 0.90,

    /*
    | Label yang menyertai tiap jumlah bintang. Disimpan di sini, bukan
    | ditanam di tampilan, supaya penyebutannya seragam di halaman produk
    | maupun di modal penilaian.
    */
    'label_bintang' => [
        1 => 'Sangat Kurang',
        2 => 'Kurang',
        3 => 'Cukup',
        4 => 'Bagus',
        5 => 'Sangat Bagus',
    ],

];
