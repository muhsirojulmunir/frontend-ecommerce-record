<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Satu sumber dana hanya boleh dibukukan sekali.
 *
 * Penjagaan terhadap pembukuan ganda sebelumnya hanya berupa pemeriksaan di
 * PHP: "sudah pernah dibukukan?" lalu "bukukan". Dua permintaan yang datang
 * bersamaan bisa sama-sama lolos pemeriksaan itu sebelum salah satunya sempat
 * menulis — dan pesanan yang sama menghasilkan DUA pengembalian dana.
 *
 * Pemeriksaan di PHP tetap dipertahankan karena pesan galatnya lebih ramah.
 * Indeks ini lapis terakhirnya: basis data yang memutuskan, dan basis data
 * tidak bisa didahului.
 *
 * Baris tanpa referensi (mis. penyesuaian manual admin) tidak terpengaruh —
 * MySQL mengizinkan banyak NULL pada indeks unik.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rpay_transactions', function (Blueprint $table) {
            $table->unique(
                ['reference_type', 'reference_id', 'source'],
                'rpay_transaksi_sumber_unik'
            );
        });
    }

    public function down(): void
    {
        Schema::table('rpay_transactions', function (Blueprint $table) {
            $table->dropUnique('rpay_transaksi_sumber_unik');
        });
    }
};
