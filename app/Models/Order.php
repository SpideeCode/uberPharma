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
}
