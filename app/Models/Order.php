<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pharmacy_id',
        'courier_id',
        'status',
        'total_price',
        'payment_status',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
    ];

    // relations éventuelles
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    /**
     * Relation avec la livraison
     */
    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    /**
     * Relation avec les avis
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Options de statut disponibles
     */
    public static function getStatusOptions()
    {
        return [
            'pending' => 'En attente',
            'processing' => 'En cours de traitement',
            'ready_for_delivery' => 'Prête pour livraison',
            'in_delivery' => 'En cours de livraison',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
            'refunded' => 'Remboursée',
        ];
    }

    /**
     * Couleurs associées aux statuts
     */
    public static function getStatusColors()
    {
        return [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'ready_for_delivery' => 'bg-indigo-100 text-indigo-800',
            'in_delivery' => 'bg-purple-100 text-purple-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'refunded' => 'bg-gray-100 text-gray-800',
        ];
    }

    /**
     * Récupère le libellé du statut
     */
    public function getStatusLabelAttribute()
    {
        return self::getStatusOptions()[$this->status] ?? $this->status;
    }

    /**
     * Récupère la couleur du statut
     */
    public function getStatusColorAttribute()
    {
        return self::getStatusColors()[$this->status] ?? 'bg-gray-100 text-gray-800';
    }
}
