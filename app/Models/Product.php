<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    public function pharmacy()
{
    return $this->belongsTo(Pharmacy::class);
}

public function orders()
{
    return $this->belongsToMany(Order::class, 'order_items')
                ->withPivot('quantity')
                ->withTimestamps();
}

}

