<?php
namespace App\Exports;

use App\Models\Order;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ShippingOrdersExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    // ✅ HEADINGS
    public function headings(): array
    {
        return [
            'Order ID',
            'Customer Name',
            'Phone',
            'Address',
            'Product Codes',
            'Total Amount',
            'Waybill Number',
            'Status',
            'Shipping Date',
        ];
    }

    // ✅ Format phone numbers (like PendingOrdersExport)
    private function formatPhone($number)
    {
        $number = preg_replace('/[\s\-\(\)]/', '', $number);

        if (str_starts_with($number, '+94')) return '0' . substr($number, 3);
        if (str_starts_with($number, '94'))   return '0' . substr($number, 2);
        if (str_starts_with($number, '0'))    return $number;

        return '0' . $number;
    }

    // ✅ DATA
    public function collection()
    {
        $query = Order::where('status', 'shipping')
            ->with('customer', 'items.product');

        // Apply date filter (like pending)
        if ($this->request->filled('date')) {
            $query->whereDate('shipping_at', $this->request->date);
        }

        return $query->get()->map(function ($order) {
            $productCodes = $order->items->map(function ($item) {
                return ($item->product->product_code ?? 'N/A') . ' x' . $item->quantity;
            })->implode(', ');

            return [
                $order->id,
                $order->customer->full_name ?? '',
                $this->formatPhone($order->customer->phone_number ?? ''),
                $order->customer->street_address ?? '',
                $productCodes,
                number_format($order->total_amount, 2),
                $order->waybill_number ?? '',
                ucfirst($order->status),
                optional($order->status_date)->format('Y-m-d H:i'),
            ];
        });
    }
}
