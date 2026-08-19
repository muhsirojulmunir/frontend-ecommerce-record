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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('size'); // 33, 34, 35, 36, 37, 38, 39, 40, 41, 42
            $table->string('color'); // Hitam Putih, Navy, Abu-abu, dll
            $table->string('color_hex')->nullable(); // #000000 untuk swatch
            $table->integer('stock')->default(0);
            $table->decimal('price_adjustment', 12, 2)->default(0); // Selisih harga dari base price
            $table->string('sku')->unique()->nullable();
            $table->timestamps();

            // Unique constraint: satu produk tidak boleh punya duplikat size+color
            $table->unique(['product_id', 'size', 'color']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
