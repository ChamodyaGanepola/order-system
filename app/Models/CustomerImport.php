<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerImport extends Model
{
    protected $fillable = [
        'customer_id',
        'imported_at',
        'import_batch',
        'product_code',
    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
