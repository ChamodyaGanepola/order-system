<?php
namespace App\Models;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'total_amount',
        'status',
        'waybill_number',
        'delivery_service',

    ];
    protected $dates = [
        'pending_at',
        'shipping_at',
        'completed_at',
        'rejected_at',
        'out_of_stock_at',
    ];


public function getStatusDateAttribute()
{
    $date = match ($this->status) {
        'pending'      => $this->pending_at,
        'shipping'     => $this->shipping_at,
        'completed'    => $this->completed_at,
        'rejected'     => $this->rejected_at,
        'out_of_stock' => $this->out_of_stock_at,
        default        => null,
    };

    return $date ? Carbon::parse($date) : null;
}
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

}
