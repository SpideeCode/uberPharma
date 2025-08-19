<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price_at_addition',
    ];

    /**
     * Le panier auquel appartient cet item
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Le produit ajouté dans le panier
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
