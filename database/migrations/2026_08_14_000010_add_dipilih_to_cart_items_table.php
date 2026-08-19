<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda "ikut dibayar" pada tiap baris keranjang.
 *
 * Sebelum ini, checkout selalu mengambil SELURUH isi keranjang. Akibatnya
 * tombol "Beli Sekarang" — yang diam-diam menaruh barang ke keranjang lalu
 * mengalihkan ke checkout — ikut menyeret semua barang lama yang belum tentu
 * mau dibayar sekarang.
 *
 * Dengan kolom ini, keranjang menjadi tempat menyimpan, dan yang dibayar hanya
 * baris yang dicentang. "Beli Sekarang" cukup mencentang satu baris saja,
 * sehingga barang lain tetap aman tersimpan, bukan terhapus atau terbawa.
 *
 * Bawaannya true supaya keranjang yang sudah ada sekarang berperilaku persis
 * seperti sebelumnya — tidak ada pembeli yang tiba-tiba mendapati keranjangnya
 * kosong saat checkout.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->boolean('dipilih')->default(true)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('dipilih');
        });
    }
};
