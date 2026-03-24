@extends('layouts.app')

@section('content')
<div class="container my-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h2 class="d-flex align-items-center gap-2">
            <i class="fas fa-receipt text-primary"></i> Order #{{ $order->id }}
        </h2>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- Customer Info -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-user"></i> Customer Info
        </div>
        <div class="card-body">
            <p><strong>Name:</strong> {{ $order->customer->full_name }}</p>
            <p><strong>Address:</strong> {{ $order->customer->street_address }}</p>
             <p><strong>City:</strong> {{ $order->customer->city ?? 'N/A' }}</p>
            <p><strong>District:</strong> {{ $order->customer->district ?? 'N/A' }}</p>
            <p><strong>Province:</strong> {{ $order->customer->province ?? 'N/A' }}</p>
            <p><strong>Phone:</strong> {{ $order->customer->phone_number }}</p>
            @if($order->customer->phone_number_2)
                <p><strong>Phone 2:</strong> {{ $order->customer->phone_number_2 }}</p>
            @endif
        </div>
    </div>

    <!-- Products Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <i class="fas fa-boxes"></i> Ordered Products
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product Code</th>
                         <th>Product name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->product_code }}</td>
                         <td>{{ $item->product->name }}</td>
                       
                        <td>Rs. {{ number_format($item->price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rs. {{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span class="fw-bold">Total Amount:</span>
            <span class="fs-5 fw-bold text-success">Rs. {{ number_format($order->total_amount, 2) }}</span>
        </div>
    </div>

    <!-- Order Status -->
    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <span><strong>Status:</strong></span>
            @php
                $statusColors = [
                    'pending'       => 'warning',
                    'shipping'      => 'info',
                    'completed'     => 'success',
                    'rejected'      => 'danger',
                    'out_of_stock'  => 'secondary'
                ];
            @endphp
            <span class="badge bg-{{ $statusColors[$order->status] ?? 'dark' }}">
                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
            </span>
        </div>
    </div>

    <!-- Shipping Details (if shipping) -->
    @if($order->status === 'shipping')
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <i class="fas fa-truck"></i> Shipping Details
        </div>
        <div class="card-body">
            <p><strong>Delivery Service:</strong> {{ $order->delivery_service ?? 'N/A' }}</p>
            <p><strong>Waybill Number:</strong>
                @if($order->waybill_number)

                        {{ $order->waybill_number }}

                @else
                    N/A
                @endif
            </p>
        </div>
    </div>
    @endif

</div>
@endsection
