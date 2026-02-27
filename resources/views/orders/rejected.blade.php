@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1><i class="fas fa-times-circle"></i> Rejected Orders</h1>
</div>

@if($orders->count() > 0)
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
                <span style="background: #fee2e2; color: #7f1d1d; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                    <i class="fas fa-times-circle"></i> Rejected
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="empty-state">
    <i class="fas fa-times-circle"></i>
    <h3>No Rejected Orders</h3>
    <p>Great! No orders have been rejected</p>
</div>
@endif
@endsection
