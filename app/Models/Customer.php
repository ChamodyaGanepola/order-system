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
    'product_code',
    'unknown_product_code',
    'city',
    'district',
    'province',
     'row_order',
      'import_batch',
];
    public function orders()
{
    return $this->hasMany(Order::class);
}
}

