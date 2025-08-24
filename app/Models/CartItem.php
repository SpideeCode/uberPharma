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
        'line_total',
    ];

    protected $casts = [
        'quantity'          => 'integer',
        'price_at_addition' => 'decimal:2',
        'line_total'        => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Boot method pour calcul auto du line_total
    |--------------------------------------------------------------------------
    */
    protected static function booted()
    {
        static::saving(function (CartItem $item) {
            $item->line_total = $item->quantity * $item->price_at_addition;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Augmenter la quantité
     */
    public function incrementQuantity(int $amount = 1): void
    {
        $this->quantity += $amount;
        $this->save();
    }

    /**
     * Réduire la quantité
     */
    public function decrementQuantity(int $amount = 1): void
    {
        $this->quantity = max(1, $this->quantity - $amount);
        $this->save();
    }
}
