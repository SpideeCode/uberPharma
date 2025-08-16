<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // Clé étrangère vers orders (cascade)
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

            // Clé étrangère vers produits (nullable pour set null)
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');

            $table->integer('quantity');
            $table->decimal('price', 8, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
