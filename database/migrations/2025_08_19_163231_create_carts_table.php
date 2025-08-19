<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // lien vers l'utilisateur
            $table->foreignId('pharmacy_id')->constrained()->onDelete('cascade'); // lien vers la pharmacie
            $table->timestamps();

            $table->unique(['user_id', 'pharmacy_id']); // un panier par pharmacie par utilisateur
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
