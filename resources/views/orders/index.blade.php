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
            <th>Order ID</th>
            <th>Customer</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($orders as $order)
        <tr>
            <td><strong>#{{ $order->id }}</strong></td>
            <td>{{ $order->customer->full_name }}</td>
            <td><strong>${{ number_format($order->total_amount, 2) }}</strong></td>
            <td>
                <!-- Status Form -->
                <form action="{{ route('orders.updateStatus', $order) }}" method="POST">
                    @csrf
<select name="status" onchange="handleStatusChange(this, '{{ $order->id }}')">

    @if($order->status === 'pending')
        <option value="pending" selected>Pending</option>
        <option value="shipping">Shipping</option>
        <option value="rejected">Rejected</option>
    @elseif($order->status === 'shipping')
        <option value="shipping" selected>Shipping</option>
        <option value="rejected">Rejected</option>
        <option value="pending" disabled>Pending</option>
    @elseif($order->status === 'rejected')
        <option value="rejected" selected>Rejected</option>
        <option value="shipping">Shipping</option>
        <option value="pending" disabled>Pending</option>
    @endif
</select>

                    <!-- hidden input for delivery service -->
                    <input type="hidden" name="delivery_service" id="delivery_{{ $order->id }}">
                    <button type="submit" style="display:none;" id="submit_{{ $order->id }}"></button>
                </form>
            </td>
            <td>
    <div class="action-buttons">
        <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
    </div>
</td>
        </tr>
        @endforeach
    </tbody>
</table>

<script>
function handleStatusChange(select, orderId) {
    let status = select.value;

    // confirmation
    if(!confirm("Are you sure you want to change status to " + status + "?")) {
        select.value = "{{ 'pending' }}"; // reset
        return;
    }

    // shipping requires delivery service
    if(status === "shipping") {
        let service = prompt("Enter Delivery Service (DHL, FedEx, UPS, etc):");
        if(!service) {
            alert("Delivery service is required!");
            select.value = "{{ 'pending' }}"; // reset
            return;
        }
        document.getElementById("delivery_" + orderId).value = service;
    }

    // submit the form
    document.getElementById("submit_" + orderId).click();
}
</script>

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
