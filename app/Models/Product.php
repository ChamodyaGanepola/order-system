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
       
    ];



    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
