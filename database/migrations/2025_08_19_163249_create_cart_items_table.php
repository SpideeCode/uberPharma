<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->onDelete('cascade'); // lien vers le panier
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // lien vers le produit
            $table->integer('quantity')->default(1);
            $table->decimal('price_at_addition', 10, 2); // prix stocké au moment de l'ajout
            $table->timestamps();

            $table->unique(['cart_id', 'product_id']); // un produit par panier unique
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
