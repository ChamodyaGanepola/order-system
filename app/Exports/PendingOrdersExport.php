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

    // ✅ HEADERS (column names in Excel)
    public function headings(): array
    {
        return [
            'Order ID',
            'Customer Name',
            'Phone',
            'Address',
            'Total Amount',
            'Status',
            'Pending Date',
        ];
    }

    // ✅ DATA
    public function collection()
    {
        $query = Order::where('status', 'pending')->with('customer');

        // Apply date filter (same as your page)
        if ($this->request->filled('date')) {
            $query->whereDate('pending_at', $this->request->date);
        }

        return $query->get()->map(function ($order) {
            return [
                $order->id,
                $order->customer->full_name ?? '',
                $order->customer->phone_number ?? '',
                $order->customer->street_address ?? '',
                number_format($order->total_amount, 2),
                ucfirst($order->status),
                optional($order->pending_at)->format('Y-m-d H:i'),
            ];
        });
    }
}
