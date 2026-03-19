@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1><i class="fas fa-check-circle"></i> Completed Orders</h1>
</div>

@if($orders->count() > 0)
<table>
    <thead>
        <tr>
            <th><i class="fas fa-hashtag"></i> Order ID</th>
            <th><i class="fas fa-user"></i> Customer</th>
            <th><i class="fas fa-money-bill-wave"></i> Total Amount</th>
            <th><i class="fas fa-tag"></i> Status</th>
             <th><i class="fas fa-calendar-alt"></i> Status Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($orders as $order)
        <tr>
            <td><strong>#{{ $order->id }}</strong></td>
            <td>{{ $order->customer->full_name }}</td>
            <td><strong>Rs.{{ number_format($order->total_amount, 2) }}</strong></td>
            <td>
                <span style="background: #dcfce7; color: #166534; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                    <i class="fas fa-check-circle"></i> Completed
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
    <i class="fas fa-check-circle"></i>
    <h3>No Completed Orders</h3>
    <p>Great! No orders have been completed yet</p>
</div>
@endif
@endsection
