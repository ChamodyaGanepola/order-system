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
        'other'   // add this
    ];

    protected $casts = [
        'other' => 'array'   // store specifications as JSON array
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
