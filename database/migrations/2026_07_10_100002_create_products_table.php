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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->json('details')->nullable(); // Bullet point details (Dibuat di Indonesia, Material, dll)
            $table->decimal('price', 12, 2);
            $table->decimal('original_price', 12, 2)->nullable(); // Harga sebelum diskon (strikethrough)
            $table->integer('stock')->default(0);
            $table->string('image'); // Gambar utama
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['active', 'inactive', 'sold_out'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
