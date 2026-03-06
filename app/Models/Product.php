<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'stock',
        'product_code',
        'other'
    ];

    protected $casts = [
        'other' => 'string' // simple string variant
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
