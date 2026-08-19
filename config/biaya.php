<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Markup Ongkos Kirim
    |--------------------------------------------------------------------------
    |
    | Tarif dari Biteship dinaikkan sekian persen sebelum ditagihkan ke pembeli.
    | Selisihnya menjadi keuntungan toko dan dicatat per pesanan pada kolom
    | shipping_markup_profit.
    |
    | Contoh dengan 25%: Biteship menagih Rp 8.000, pembeli membayar Rp 10.000,
    | keuntungan Rp 2.000.
    |
    | Hasilnya dibulatkan ke atas ke kelipatan "pembulatan" supaya angka
    | ongkirnya tidak ganjil seperti Rp 10.437 — angka ganjil membuat pembeli
    | curiga ada biaya tersembunyi, padahal itu cuma hasil perkalian.
    |
    | Setel 0 untuk mematikan markup sepenuhnya.
    |
    */

    'markup_ongkir_persen' => 25,
    'pembulatan_ongkir'    => 500,

    /*
    |--------------------------------------------------------------------------
    | Biaya Midtrans
    |--------------------------------------------------------------------------
    |
    | Tarifnya sendiri ada di App\Services\TransactionFeeService, sebab
    | perhitungannya berbeda-beda menurut kanal pembayaran dan tidak bisa
    | diwakili satu angka.
    |
    | Biaya dihitung ulang saat pembayaran DIKONFIRMASI, memakai kanal yang
    | benar-benar dipakai menurut Midtrans — bukan yang dipilih pembeli di
    | halaman checkout. Keduanya bisa berbeda, dan yang menagih biaya ke toko
    | adalah kanal yang sungguh terpakai.
    |
    | Selama pesanan belum lunas, biayanya tetap ditampilkan di panel admin
    | tetapi ditandai sebagai perkiraan.
    |
    */

    'hitung_biaya_saat_lunas' => true,

];
