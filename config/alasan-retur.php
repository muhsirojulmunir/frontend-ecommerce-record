<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Alasan Pengajuan Pengembalian
    |--------------------------------------------------------------------------
    |
    | Pembeli memilih salah satu alasan di bawah, lalu tetap wajib menuliskan
    | penjelasannya sendiri. Alasan terpilih disimpan sebagai kode agar bisa
    | dihitung untuk laporan; penjelasan bebasnya disimpan terpisah.
    |
    | 'penyelesaian' membatasi pilihan yang masuk akal untuk alasan itu.
    | Misalnya "berubah pikiran" tidak pantas diselesaikan dengan tukar
    | barang, karena barangnya sendiri tidak bermasalah.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Bukti Wajib
    |--------------------------------------------------------------------------
    |
    | Setiap pengajuan wajib disertai foto resi, foto paket, dan video unboxing.
    | Ketiganya memastikan barang yang dipersoalkan memang barang yang kami
    | kirim, dan kondisinya terekam sejak paket pertama kali dibuka.
    |
    | Batas video 2 menit disepakati agar cukup merekam proses membuka paket
    | tanpa membuat berkasnya terlalu besar untuk diunggah dari ponsel.
    |
    | Berkas yang melebihi batas TIDAK langsung ditolak: peramban memampatkannya
    | dulu sampai muat. Angka di bawah karena itu berlaku dua kali — sebagai
    | sasaran pemampatan di peramban, dan sebagai batas terakhir di peladen.
    |
    | Catatan: batas ukuran unggahan juga dijaga oleh PHP lewat upload_max_filesize
    | dan post_max_size di php.ini. Kalau nilai di sini dinaikkan melewati
    | keduanya, berkas besar akan ditolak oleh PHP sebelum Laravel sempat
    | memeriksanya.
    |
    */

    'bukti' => [
        'maks_foto_mb'      => 2,
        'maks_video_mb'     => 10,
        'maks_durasi_detik' => 120,

        /*
         * Kelonggaran untuk pemampatan.
         *
         * Pemampat menyasar sedikit di bawah batas, bukan tepat di batasnya.
         * Ukuran hasil MediaRecorder tidak bisa dipastikan persis — bitrate
         * yang diminta hanya sasaran, bukan janji — jadi membidik tepat 10 MB
         * berarti sebagian hasil akan meleset ke 10,3 MB dan ditolak peladen
         * setelah pembeli terlanjur menunggu proses yang lama.
         */
        'sasaran_pemampatan' => 0.90,

        // Sisi terpanjang foto setelah dimampatkan. Foto ponsel masa kini
        // kerap 4000 piksel lebih — jauh melebihi yang diperlukan untuk
        // membaca nomor resi, dan itulah sumber utama besarnya berkas.
        'maks_sisi_foto'     => 2200,
    ],

    'pilihan' => [
        [
            'kode'         => 'ukuran_tidak_pas',
            'label'        => 'Ukuran tidak pas',
            'keterangan'   => 'Sepatu kekecilan atau kebesaran saat dicoba.',
            'penyelesaian' => ['exchange', 'refund'],
        ],
        [
            'kode'         => 'barang_rusak',
            'label'        => 'Barang rusak atau cacat',
            'keterangan'   => 'Ada sobek, lem terbuka, jahitan lepas, atau cacat produksi.',
            'penyelesaian' => ['refund', 'exchange'],
        ],
        [
            'kode'         => 'salah_kirim',
            'label'        => 'Barang yang dikirim tidak sesuai pesanan',
            'keterangan'   => 'Warna, model, atau ukurannya berbeda dari yang dipesan.',
            'penyelesaian' => ['exchange', 'refund'],
        ],
        [
            'kode'         => 'tidak_sesuai_deskripsi',
            'label'        => 'Tidak sesuai deskripsi di web',
            'keterangan'   => 'Bahan, warna, atau bentuknya berbeda dari foto dan keterangan.',
            'penyelesaian' => ['refund', 'exchange'],
        ],
        [
            'kode'         => 'kurang_lengkap',
            'label'        => 'Barang kurang atau tidak lengkap',
            'keterangan'   => 'Jumlahnya kurang, atau kelengkapan seperti tali sepatu tidak ada.',
            'penyelesaian' => ['refund', 'exchange'],
        ],
        [
            'kode'         => 'berubah_pikiran',
            'label'        => 'Berubah pikiran',
            'keterangan'   => 'Barang sesuai, tetapi tidak jadi dipakai.',
            'penyelesaian' => ['refund'],
        ],
        [
            'kode'         => 'lainnya',
            'label'        => 'Alasan lain',
            'keterangan'   => 'Tuliskan alasannya pada kolom penjelasan.',
            'penyelesaian' => ['refund', 'exchange'],
        ],
    ],

    /*
    | Batas waktu pengajuan, dihitung sejak pesanan ditandai selesai.
    */
    'batas_hari' => 7,

    /*
    | Penjelasan bebas bersifat opsional — alasan yang dipilih dari daftar
    | sudah cukup menjelaskan duduk perkaranya. Kecuali untuk kode di bawah,
    | yang tidak memberi tahu apa pun tanpa penjelasan pembeli.
    */
    'wajib_penjelasan' => 'lainnya',

    /*
    | Panjang minimal penjelasan, berlaku hanya bila penjelasannya diisi.
    */
    'minimal_penjelasan' => 15,

    /*
    |--------------------------------------------------------------------------
    | Tenggat & Alamat Pengembalian
    |--------------------------------------------------------------------------
    */

    // Perkiraan lama admin meninjau pengajuan, dalam hari kerja.
    'konfirmasi_hari_kerja' => 2,

    // Batas waktu pembeli mengirim barangnya kembali setelah disetujui.
    'batas_kirim_balik_hari' => 7,

    /*
    | Alamat tujuan pengiriman barang kembali. Ongkos kirimnya ditanggung
    | pembeli, jadi kurirnya bebas — yang penting resinya dicatat agar
    | paketnya bisa ditelusuri kalau tidak kunjung sampai.
    */
    'alamat_pengembalian' => [
        'nama'      => env('RETURN_ADDRESS_NAME', 'Gudang RECORD — Bagian Pengembalian'),
        'telepon'   => env('RETURN_ADDRESS_PHONE', '081234567890'),
        'alamat'    => env('RETURN_ADDRESS_LINE', 'Jl. Kyai Tambak Deres No. 30'),
        'kelurahan' => env('RETURN_ADDRESS_VILLAGE', 'Kedung Cowek'),
        'kecamatan' => env('RETURN_ADDRESS_DISTRICT', 'Bulak'),
        'kota'      => env('RETURN_ADDRESS_CITY', 'Kota Surabaya'),
        'provinsi'  => env('RETURN_ADDRESS_PROVINCE', 'Jawa Timur'),
        'kode_pos'  => env('RETURN_ADDRESS_POSTAL', '60129'),
    ],

    /*
    | Syarat barang agar pengembalian dana disetujui setelah diperiksa.
    | Ditampilkan apa adanya ke pembeli sebelum ia mengirim barangnya,
    | supaya tidak ada yang merasa dijebak saat pengajuannya ditolak.
    */
    'syarat_barang' => [
        'Barang belum dipakai di luar ruangan dan solnya masih bersih.',
        'Label, kartu, dan kelengkapan bawaan masih utuh dan ikut dikirim.',
        'Dus asli tidak rusak, tidak disobek, dan tidak dipakai sebagai bungkus luar.',
        'Tidak ada bekas coretan, sablon tambahan, atau perbaikan sendiri.',
    ],

];
