<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pharmacy_id',
    ];

    /**
     * L'utilisateur qui possède ce panier
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * La pharmacie de ce panier
     */
    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    /**
     * Les produits de ce panier
     */
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
}
