<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'street_address',
        'phone_number',
        'phone_number_2',
        'other',
        'product_code',
    ];
    public function orders()
{
    return $this->hasMany(Order::class);
}
}

