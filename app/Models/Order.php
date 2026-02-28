<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
   protected $fillable = [
    'customer_id',
    'total_amount',
    'status',
    'delivery_service'
];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

}
