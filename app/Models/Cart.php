<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pharmacy_id',
        'is_active',
        'currency',
        'subtotal',
        'delivery_fee',
        'service_fee',
        'discount_total',
        'total',
        'locked_at',
        'expires_at',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'locked_at'   => 'datetime',
        'expires_at'  => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Recalculer les totaux du panier
     */
    public function recalcTotals(): void
    {
        $subtotal = $this->items->sum(fn ($item) => $item->line_total);

        $this->subtotal = $subtotal;
        $this->total = max(0, $subtotal + $this->delivery_fee + $this->service_fee - $this->discount_total);

        $this->save();
    }

    /**
     * Vérifie si le panier est expiré
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
