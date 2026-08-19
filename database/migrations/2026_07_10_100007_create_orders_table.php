<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->decimal('total_price', 12, 2); // Total harga barang
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2); // total_price + shipping_cost
            $table->enum('status', ['pending', 'processing', 'shipped', 'completed', 'cancelled'])->default('pending');
            $table->json('shipping_address'); // Snapshot alamat pengiriman
            $table->string('courier')->nullable(); // Nama kurir (JNE, J&T, dll)
            $table->string('tracking_number')->nullable(); // Nomor resi
            $table->string('payment_method'); // BCA, BNI, Indomaret, Alfamart, COD
            $table->string('payment_status', 50)->default('unpaid');
            $table->text('notes')->nullable(); // Catatan dari customer
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name'); // Snapshot nama produk saat checkout
            $table->string('variant_info')->nullable(); // Snapshot "Size 34 - Hitam Putih"
            $table->integer('quantity');
            $table->decimal('price', 12, 2); // Harga satuan saat checkout
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
