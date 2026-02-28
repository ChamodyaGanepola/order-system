@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1><i class="fas fa-hourglass-half"></i> Pending Orders</h1>
</div>

@if($orders->count() > 0)
<form method="GET" style="margin-bottom: 10px;">
    <label for="per_page">Show:</label>
    <select name="per_page" id="per_page" onchange="this.form.submit()">
        <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
    </select>
</form>


<table>
    <thead>
        <tr>
            <th><i class="fas fa-hashtag"></i> Order ID</th>
            <th><i class="fas fa-user"></i> Customer</th>
            <th><i class="fas fa-money-bill-wave"></i> Total Amount</th>
            <th><i class="fas fa-tag"></i> Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($orders as $order)
        <tr>
            <td><strong>#{{ $order->id }}</strong></td>
            <td>{{ $order->customer->full_name }}</td>
            <td><strong>${{ number_format($order->total_amount, 2) }}</strong></td>
            <td>
                <span style="background: #fef3c7; color: #92400e; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                    <i class="fas fa-hourglass-half"></i> Pending
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
   <div class="d-flex justify-content-center mt-3">
    {{ $orders->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
@else
<div class="empty-state">
    <i class="fas fa-hourglass-half"></i>
    <h3>No Pending Orders</h3>
    <p>All orders are on track!</p>
</div>
@endif
@endsection
