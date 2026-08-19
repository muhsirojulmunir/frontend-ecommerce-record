<?php

/*
|--------------------------------------------------------------------------
| Isi Halaman FAQ
|--------------------------------------------------------------------------
|
| Ubah daftar di bawah ini untuk menambah, mengubah, atau menghapus
| pertanyaan yang tampil di halaman /faq. Susunannya:
|
|   'Nama Grup' => [
|       ['t' => 'Pertanyaan', 'j' => 'Jawaban'],
|       ...
|   ]
|
| Jawaban boleh memuat tag HTML sederhana seperti <strong>, <br>, <a>,
| serta <ul><li> untuk daftar berpoin.
|
| Setelah mengubah berkas ini, jalankan: php artisan config:clear
|
*/

return [

    'Informasi Umum' => [
        [
            't' => 'Bagaimana cara membuat akun di website RECORD?',
            'j' => 'Klik ikon profil di pojok kanan atas, lalu pilih <strong>Daftar</strong>. '
                 . 'Isi nama, email, dan kata sandi, kemudian klik tombol daftar. '
                 . 'Setelah itu kamu bisa langsung berbelanja dan memantau pesananmu.',
        ],
        [
            't' => 'Lupa kata sandi, apa yang harus saya lakukan?',
            'j' => 'Pada halaman masuk, klik tautan <strong>Lupa kata sandi?</strong>. '
                 . 'Masukkan email yang kamu pakai saat mendaftar, lalu kami kirimkan tautan '
                 . 'untuk membuat kata sandi baru. Cek juga folder spam bila email belum masuk.',
        ],
        [
            't' => 'Apakah harus punya akun untuk berbelanja?',
            'j' => 'Sangat disarankan. Dengan akun, kamu bisa menyimpan alamat pengiriman, '
                 . 'melihat riwayat pesanan, dan melacak status pengiriman kapan saja.',
        ],
        [
            't' => 'Bagaimana cara menghapus akun saya?',
            'j' => 'Masuk ke menu <strong>Profil</strong>, gulir ke bagian paling bawah, '
                 . 'lalu pilih <strong>Hapus Akun</strong>. Perlu diketahui, riwayat pesanan '
                 . 'akan ikut terhapus dan tindakan ini tidak bisa dibatalkan.',
        ],
    ],

    'Pemesanan' => [
        [
            't' => 'Bagaimana cara memesan produk?',
            'j' => 'Pilih produk yang kamu mau, tentukan <strong>ukuran dan warnanya</strong>, '
                 . 'lalu klik Tambah ke Keranjang. Setelah semua produk terkumpul, buka keranjang '
                 . 'dan klik Checkout untuk mengisi alamat serta memilih metode pembayaran.',
        ],
        [
            't' => 'Bagaimana cara mengetahui ukuran yang pas?',
            'j' => 'Setiap halaman produk mencantumkan pilihan ukuran yang tersedia. '
                 . 'Bila masih ragu, hubungi kami lewat WhatsApp dan sebutkan panjang telapak kaki '
                 . 'kamu dalam sentimeter — kami bantu carikan ukuran yang paling sesuai.',
        ],
        [
            't' => 'Apakah pesanan bisa dibatalkan atau diubah?',
            'j' => 'Bisa, selama pesanan <strong>belum kami kirim</strong>. Segera hubungi kami '
                 . 'lewat WhatsApp dengan menyebutkan nomor pesananmu. Bila paket sudah diserahkan '
                 . 'ke kurir, pesanan tidak dapat diubah lagi.',
        ],
        [
            't' => 'Bagaimana melihat status pesanan saya?',
            'j' => 'Masuk ke akunmu, lalu buka menu <strong>Pesanan Saya</strong>. '
                 . 'Di sana tampil status terkini setiap pesanan beserta nomor resinya '
                 . 'bila paket sudah dikirim.',
        ],
    ],

    'Pembayaran' => [
        [
            't' => 'Metode pembayaran apa saja yang tersedia?',
            'j' => 'Kami menerima <strong>transfer bank</strong>, serta pembayaran melalui '
                 . 'kartu, e-wallet, dan gerai ritel. Untuk wilayah tertentu tersedia juga '
                 . '<strong>COD (bayar di tempat)</strong>. Semua pilihan akan muncul di halaman checkout.',
        ],
        [
            't' => 'Apakah aman berbelanja di website ini?',
            'j' => 'Aman. Proses pembayaran ditangani oleh penyedia pembayaran resmi, dan kami '
                 . 'tidak pernah menyimpan data kartu kamu. Kami juga tidak akan pernah meminta '
                 . 'kata sandi atau kode OTP melalui telepon maupun chat.',
        ],
        [
            't' => 'Perlukah melakukan konfirmasi pembayaran?',
            'j' => 'Untuk pembayaran otomatis, tidak perlu — status pesanan akan berubah sendiri. '
                 . 'Khusus transfer bank manual, kirimkan bukti transfer lewat WhatsApp agar '
                 . 'pesananmu segera kami proses.',
        ],
        [
            't' => 'Berapa lama batas waktu pembayaran?',
            'j' => 'Pesanan yang belum dibayar dalam <strong>24 jam</strong> akan dibatalkan '
                 . 'otomatis oleh sistem, dan stok akan dikembalikan agar bisa dibeli pembeli lain.',
        ],
        [
            't' => 'Bagaimana cara memakai kode promo?',
            'j' => 'Masukkan kode promo pada kolom yang tersedia di halaman checkout, lalu klik '
                 . 'terapkan. Potongan harga akan langsung terlihat pada rincian total belanja.',
        ],
    ],

    'Pengiriman' => [
        [
            't' => 'Berapa biaya pengirimannya?',
            'j' => 'Ongkos kirim dihitung otomatis berdasarkan alamat tujuan dan berat paket. '
                 . 'Nominalnya akan muncul di halaman checkout setelah kamu mengisi alamat lengkap.',
        ],
        [
            't' => 'Berapa lama pesanan sampai?',
            'j' => 'Pesanan kami proses dalam <strong>1–2 hari kerja</strong>. Setelah dikirim, '
                 . 'estimasi tibanya 2–4 hari untuk Pulau Jawa dan 3–7 hari untuk luar Jawa, '
                 . 'tergantung layanan kurir yang dipilih.',
        ],
        [
            't' => 'Kurir apa saja yang digunakan?',
            'j' => 'Kami bekerja sama dengan <strong>JNE, J&amp;T Express, dan SiCepat</strong>. '
                 . 'Pilihan kurir yang tersedia akan tampil saat checkout.',
        ],
        [
            't' => 'Bagaimana cara melacak pengiriman?',
            'j' => 'Buka menu <strong>Pesanan Saya</strong>, pilih pesanan yang ingin dilacak, '
                 . 'lalu salin nomor resi yang tertera. Nomor tersebut bisa kamu masukkan '
                 . 'di situs kurir yang bersangkutan.',
        ],
        [
            't' => 'Apakah bisa kirim ke seluruh Indonesia?',
            'j' => 'Bisa. Kami melayani pengiriman ke seluruh wilayah Indonesia selama '
                 . 'alamat tujuan terjangkau oleh kurir yang tersedia.',
        ],
    ],

    'Penukaran & Pengembalian' => [
        [
            't' => 'Apakah produk bisa ditukar ukuran?',
            'j' => 'Bisa, dalam <strong>3 hari</strong> sejak paket diterima, dengan syarat produk '
                 . 'belum dipakai, label masih utuh, dan kotak aslinya tidak rusak. '
                 . 'Ongkos kirim penukaran ditanggung pembeli.',
        ],
        [
            't' => 'Bagaimana jika produk yang diterima cacat atau salah kirim?',
            'j' => 'Mohon rekam video saat membuka paket sebagai bukti, lalu kirimkan ke kami '
                 . 'lewat WhatsApp maksimal <strong>2 x 24 jam</strong> setelah paket diterima. '
                 . 'Kami akan menggantinya tanpa biaya tambahan.',
        ],
        [
            't' => 'Berapa lama proses pengembalian dana?',
            'j' => 'Setelah barang retur kami terima dan diperiksa, dana dikembalikan dalam '
                 . '<strong>3–7 hari kerja</strong> ke rekening yang kamu berikan.',
        ],
    ],

];
