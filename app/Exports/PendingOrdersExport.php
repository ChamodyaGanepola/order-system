<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PendingOrdersExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    // ✅ HEADINGS (Excel columns)
    public function headings(): array
    {
        return [
            'Order ID',
            'Customer Name',
            'Phone',
            'Address',
            'Product Codes',
            'Total Amount',
            'Status',
            'Pending Date',
        ];
    }
    private function formatPhone($number)
{
    // Remove spaces, brackets, dashes
    $number = preg_replace('/[\s\-\(\)]/', '', $number);

    // If starts with +94 → replace with 0
    if (str_starts_with($number, '+94')) {
        return '0' . substr($number, 3);
    }

    // If starts with 94 → replace with 0
    if (str_starts_with($number, '94')) {
        return '0' . substr($number, 2);
    }

    // If already starts with 0 → keep as is
    if (str_starts_with($number, '0')) {
        return $number;
    }

    // Otherwise assume local number → add 0
    return '0' . $number;
}

    // ✅ DATA
    public function collection()
    {
        $query = Order::where('status', 'pending')
            ->with('customer', 'items.product');

        // ✅ Apply date filter (same as your UI)
        if ($this->request->filled('date')) {
            $query->whereDate('pending_at', $this->request->date);
        }

        return $query->get()->map(function ($order) {

            // ✅ Product codes with quantity
            $productCodes = $order->items->map(function ($item) {
                return ($item->product->product_code ?? 'N/A') . ' x' . $item->quantity;
            })->implode(', ');

            return [
                $order->id,
                $order->customer->full_name ?? '',
                $this->formatPhone($order->customer->phone_number ?? ''),
                $order->customer->street_address ?? '',
                $productCodes, // ✅ INCLUDED
                number_format($order->total_amount, 2),
                ucfirst($order->status),
               optional($order->status_date)->format('Y-m-d H:i'),
            ];
        });
    }
}
