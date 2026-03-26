@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1><i class="fas fa-truck"></i> Shipping Orders</h1>
</div>
<form method="GET" style="margin-bottom: 15px; display:flex; gap:10px; align-items:center;">
    <input type="date" name="date"
        value="{{ request('date', now()->toDateString()) }}"
        class="form-control">

    <select name="per_page" onchange="this.form.submit()" class="form-control">
        @foreach([5,10,25,50] as $size)
            <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>
                {{ $size }} per page
            </option>
        @endforeach
    </select>

    <button type="submit" class="btn btn-primary">Filter</button>

    @if($orders->total() > 0)
        <a href="{{ route('orders.shipping.export', request()->all()) }}"
           class="btn btn-success">
            Export Excel
        </a>
    @endif

</form>
@if($orders->count() > 0)
<table>
    <thead>
        <tr>
            <th><i class="fas fa-hashtag"></i> Order ID</th>
            <th><i class="fas fa-user"></i> Customer</th>
            <th><i class="fas fa-money-bill-wave"></i> Total Amount</th>
            <th><i class="fas fa-barcode"></i> Waybill</th>
            <th><i class="fas fa-tag"></i> Status</th>
            <th><i class="fas fa-calendar-alt"></i> Status Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($orders as $order)
        <tr>
            <td><strong>#{{ $order->id }}</strong></td>
            <td>{{ $order->customer->full_name }}</td>
            <td><strong>Rs. {{ number_format($order->total_amount, 2) }}</strong></td>
            <td>
                @if($order->waybill_number)

                        {{ $order->waybill_number }}

                @else
                    -
                @endif
            </td>
            <td>
                <span style="background: #dbeafe; color: #1e40af; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                    <i class="fas fa-truck"></i> Shipping
                </span>
            </td>
              <td>
                {{ $order->status_date ? \Carbon\Carbon::parse($order->status_date)->timezone('Asia/Colombo')->format('d M Y H:i') : '-' }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="empty-state">
    <i class="fas fa-truck"></i>
    <h3>No Shipping Orders</h3>
    <p>No orders currently being shipped</p>
</div>
@endif
@if($orders->total() > 0)
<div class="pagination-container" style="display: flex; flex-direction: column; align-items: center; gap: 8px; margin-top: 20px;">

    <!-- Numeric Pagination Links -->
    <div class="pagination-links">
        @if ($orders->hasPages())
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
        @endif
    </div>

    <!-- Summary -->
    <div class="pagination-summary" style="font-size: 14px; color: #555;">
        Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
    </div>
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
