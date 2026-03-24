@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1><i class="fas fa-times-circle"></i> Out Of Stock Orders</h1>
</div>

@if(count($outOfStockSummary) > 0)
<div class="out-of-stock-summary" style="margin-bottom:20px;">
    <h4>Out Of Stock Products Summary</h4>
    <ul>
        @foreach($outOfStockSummary as $productCode => $count)
            <li>{{ $productCode }}: {{ $count }}</li>
        @endforeach
    </ul>
</div>
@endif

@if($orders->total() > 0)
<table class="table table-striped">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Product Code</th>
            <th>Total Amount</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($orders as $order)
            @foreach($order->items as $item)
                <tr>
                    <td><strong>#{{ $order->id }}</strong></td>
                    <td>{{ $order->customer->full_name }}</td>
                    <td>{{ $item->product->product_code }}</td>
                    <td><strong>Rs.{{ number_format($order->total_amount, 2) }}</strong></td>
                    <td>
                        <span style="background:#fee2e2;color:#991b1b;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;">
                            Out Of Stock
                        </span>
                    </td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

{{-- Pagination --}}
@if($orders->hasPages())
<div style="text-align:center; margin-top:20px;">
    {{ $orders->links() }}
</div>
@endif

@else
<div class="empty-state">
    <h3>No Out Of Stock Orders</h3>
    <p>All products have enough stock.</p>
</div>
@endif

@endsection
