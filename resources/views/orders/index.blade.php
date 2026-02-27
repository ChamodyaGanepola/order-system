@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1><i class="fas fa-boxes"></i> Orders Management</h1>
    <a href="/customers/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create Order
    </a>
</div>

@if($orders->count() > 0)
<table>
    <thead>
        <tr>
            <th><i class="fas fa-hashtag"></i> Order ID</th>
            <th><i class="fas fa-user"></i> Customer</th>
            <th><i class="fas fa-money-bill-wave"></i> Total Amount</th>
            <th><i class="fas fa-tag"></i> Status</th>
            <th><i class="fas fa-cogs"></i> Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($orders as $order)
        <tr>
            <td><strong>#{{ $order->id }}</strong></td>
            <td>{{ $order->customer->full_name }}</td>
            <td><strong>${{ number_format($order->total_amount, 2) }}</strong></td>
            <td>
                @if($order->status === 'pending')
                    <span style="background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        <i class="fas fa-hourglass-half"></i> Pending
                    </span>
                @elseif($order->status === 'shipping')
                    <span style="background: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        <i class="fas fa-truck"></i> Shipping
                    </span>
                @elseif($order->status === 'rejected')
                    <span style="background: #fee2e2; color: #7f1d1d; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        <i class="fas fa-times-circle"></i> Rejected
                    </span>
                @else
                    <span style="background: #ecfdf5; color: #166534; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        <i class="fas fa-check-circle"></i> {{ ucfirst($order->status) }}
                    </span>
                @endif
            </td>
            <td>
                <div class="action-buttons">
                    <a href="/orders/{{ $order->id }}/edit" class="btn btn-secondary btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="/orders/{{ $order->id }}/view" class="btn btn-secondary btn-sm">
                        <i class="fas fa-eye"></i> View
                    </a>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="empty-state">
    <i class="fas fa-boxes"></i>
    <h3>No Orders Found</h3>
    <p>Create your first order by selecting a customer</p>
    <a href="/customers/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create Order
    </a>
</div>
@endif
@endsection
