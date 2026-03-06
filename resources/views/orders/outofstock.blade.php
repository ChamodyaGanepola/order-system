@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1><i class="fas fa-times-circle"></i> Out Of Stock Orders</h1>
</div>

@if(count($outOfStockSummary) > 0)
<div class="out-of-stock-summary" style="margin-bottom:20px;">
    <h4>Out Of Stock Products Summary</h4>
    <ul>
        @foreach($outOfStockSummary as $productVariant => $count)
            <li>{{ $productVariant }}: {{ $count }}</li>
        @endforeach
    </ul>
</div>
@endif

@if($orders->total() > 0)
<table class="table table-striped">
    <thead>
        <tr>
            <th><i class="fas fa-hashtag"></i> Order ID</th>
            <th><i class="fas fa-user"></i> Customer</th>
            <th><i class="fas fa-box"></i> Product Code</th>
            <th><i class="fas fa-palette"></i> Variant</th>
            <th><i class="fas fa-money-bill-wave"></i> Total Amount</th>
            <th><i class="fas fa-tag"></i> Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($orders as $order)
            @foreach($order->items as $item)
                @php
                    $product = $item->product;

                    // Normalize variant display
                    $variant = $product->other;

                    if (is_string($variant)) {
                        $decoded = json_decode($variant, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $variant = implode(', ', $decoded);
                        }
                    } elseif (is_array($variant)) {
                        $variant = implode(', ', $variant);
                    }

                    if (empty($variant)) {
                        $variant = 'N/A';
                    }
                @endphp
                <tr>
                    <td><strong>#{{ $order->id }}</strong></td>
                    <td>{{ $order->customer->full_name }}</td>
                    <td>{{ $product->product_code }}</td>
                    <td>{{ $variant }}</td>
                    <td><strong>${{ number_format($order->total_amount, 2) }}</strong></td>
                    <td>
                        <span style="background:#fee2e2;color:#991b1b;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;">
                            <i class="fas fa-times-circle"></i> Out Of Stock
                        </span>
                    </td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

<!-- Custom Numeric Pagination -->
@if($orders->hasPages())
<div class="pagination-container" style="display: flex; flex-direction: column; align-items: center; gap: 8px; margin-top: 20px;">

    <div class="pagination-links">
        <ul class="pagination">
            {{-- Previous --}}
            @if ($orders->onFirstPage())
                <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $orders->previousPageUrl() }}">&laquo;</a></li>
            @endif

            {{-- Page Numbers --}}
            @foreach(range(1, $orders->lastPage()) as $page)
                @if($page == $orders->currentPage())
                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $orders->url($page) }}">{{ $page }}</a></li>
                @endif
            @endforeach

            {{-- Next --}}
            @if ($orders->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $orders->nextPageUrl() }}">&raquo;</a></li>
            @else
                <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
            @endif
        </ul>
    </div>

    <div class="pagination-summary" style="font-size: 14px; color: #555;">
        Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
    </div>
</div>
@endif

@else
<div class="empty-state">
    <i class="fas fa-times-circle"></i>
    <h3>No Out Of Stock Orders</h3>
    <p>All products have enough stock.</p>
</div>
@endif

<style>
.pagination-links .pagination {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.pagination-links .page-item .page-link {
    padding: 6px 12px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    color: #1e293b;
    transition: all 0.2s;
}

.pagination-links .page-item.active .page-link {
    background-color: #2563eb;
    color: white;
    border-color: #2563eb;
}

.pagination-links .page-item.disabled .page-link {
    color: #b0b0b0;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .pagination-container {
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
}
</style>

@endsection
