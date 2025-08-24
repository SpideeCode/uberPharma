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
            $table->foreignId('cart_id')->constrained('carts')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Sauvegarde au moment de l’ajout
            $table->string('product_name')->nullable();
            $table->decimal('price_at_addition', 10, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('line_total', 10, 2)->default(0);

            // Extra infos (par ex : dosage, pack, options…)
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['cart_id', 'product_id']); // pas 2x le même produit dans un panier
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
