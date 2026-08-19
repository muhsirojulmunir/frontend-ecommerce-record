<?php

return [

    'nama' => 'R_Pay',

    /*
    |--------------------------------------------------------------------------
    | Pencairan ke Rekening Bank
    |--------------------------------------------------------------------------
    */

    'pencairan' => [
        'minimum'      => 50000,
        'hari_kerja'   => 2,   // perkiraan paling lama, dalam hari kerja
        'hari_kerja_minimal' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tanggal Merah
    |--------------------------------------------------------------------------
    |
    | Hari Minggu selalu dilewati secara otomatis. Daftar di bawah dipakai
    | untuk hari libur nasional yang jatuh pada hari kerja. Sabtu dianggap
    | hari kerja untuk perbankan daring; ubah 'sabtu_libur' bila tidak.
    |
    | Perbarui daftarnya setiap awal tahun mengikuti keputusan pemerintah.
    |
    */

    'sabtu_libur' => true,

    'tanggal_merah' => [
        '2026-01-01', // Tahun Baru Masehi
        '2026-03-19', // Hari Raya Nyepi
        '2026-03-20', // Idul Fitri
        '2026-03-21', // Idul Fitri
        '2026-04-03', // Wafat Isa Almasih
        '2026-05-01', // Hari Buruh
        '2026-05-14', // Kenaikan Isa Almasih
        '2026-05-27', // Idul Adha
        '2026-06-01', // Hari Lahir Pancasila
        '2026-06-16', // Tahun Baru Islam
        '2026-08-17', // Hari Kemerdekaan
        '2026-08-25', // Maulid Nabi
        '2026-12-25', // Hari Natal
    ],

    /*
    |--------------------------------------------------------------------------
    | Bank yang Didukung
    |--------------------------------------------------------------------------
    */

    'bank' => [
        'BCA', 'BNI', 'BRI', 'Mandiri', 'CIMB Niaga', 'Permata',
        'Danamon', 'BTN', 'BSI', 'Maybank', 'OCBC NISP', 'Panin',
        'SeaBank', 'Jago', 'Blu BCA Digital', 'Bank Jatim',
    ],

];
