<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambahkan kolom tracking biaya transaksi ke tabel orders:
 *
 *  midtrans_fee          – Biaya payment gateway Midtrans (sudah termasuk PPN 11%)
 *  shipping_actual_cost  – Tarif kurir aktual dari Biteship API (tanpa markup)
 *  shipping_markup_profit– Keuntungan toko dari markup ongkir
 *  net_revenue           – Estimasi pendapatan bersih (grand_total - midtrans_fee)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('midtrans_fee', 12, 2)->default(0)->after('grand_total');
            $table->decimal('shipping_actual_cost', 12, 2)->default(0)->after('midtrans_fee');
            $table->decimal('shipping_markup_profit', 12, 2)->default(0)->after('shipping_actual_cost');
            $table->decimal('net_revenue', 12, 2)->nullable()->after('shipping_markup_profit');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'midtrans_fee',
                'shipping_actual_cost',
                'shipping_markup_profit',
                'net_revenue',
            ]);
        });
    }
};
