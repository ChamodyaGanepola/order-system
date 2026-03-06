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
            <th>Tracking Number</th>
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
            @if(in_array($order->status, ['pending', 'shipping']))
                <form action="{{ route('orders.updateStatus', $order) }}" method="POST">
                    @csrf
                    <select name="status" onchange="handleStatusChange(this, '{{ $order->id }}')">
                        @if($order->status === 'pending')
                            <option value="pending" selected>Pending</option>
                            <option value="shipping">Shipping</option>
                            <option value="rejected">Rejected</option>
                        @elseif($order->status === 'shipping')
                            <option value="shipping" selected>Shipping</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Rejected</option>
                        @endif
                    </select>
                    <input type="hidden" name="delivery_service" id="delivery_{{ $order->id }}">
                    <button type="submit" style="display:none;" id="submit_{{ $order->id }}"></button>
                </form>
            @else
                @php
                    $statusColors = [
                        'completed'     => 'success',
                        'rejected'      => 'danger',
                        'out_of_stock'  => 'warning',
                        'pending'       => 'primary',
                        'shipping'      => 'info',
                    ];
                @endphp
                <span class="btn btn-{{ $statusColors[$order->status] ?? 'secondary' }} btn-sm" style="pointer-events: none;">
                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                </span>
            @endif
            </td>
            <td>
        {{ $order->tracking_number ?? '-' }} <!-- NEW -->
    </td>
            <td>
                <div class="action-buttons">
                    @if(in_array($order->status, ['pending', 'shipping', 'out_of_stock']))
                    <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    @endif
                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> View Order
                    </a>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@if($orders->total() > 0)
<div class="pagination-container" style="margin-top: 20px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
    <div class="pagination-links">
        {{ $orders->links('vendor.pagination.custom') }}
    </div>
    <div class="pagination-summary" style="font-size: 14px; color: #555;">
        Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
    </div>
</div>
@endif

<!-- Delivery Service Card -->
<!-- Shipping Info Card (Initially Hidden) -->
<div id="shipping-card" style="display:none; position: fixed; top: 50%; left: 50%;
    transform: translate(-50%, -50%); background: white; padding: 20px;
    border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); z-index: 9999; width: 300px;">
    <h4>Shipping Details</h4>
    <form id="shipping-form">
        <div class="form-group" style="margin-bottom:10px;">
            <label for="delivery_service">Delivery Service</label>
            <input type="text" id="delivery_service_input" class="form-control" placeholder="e.g., Koobiyo">
        </div>
        <div class="form-group" style="margin-bottom:10px;">
            <label for="tracking_number">Tracking Number (optional)</label>
            <input type="text" id="tracking_number_input" class="form-control" placeholder="Enter tracking number">
        </div>
        <div style="display:flex; justify-content: flex-end; gap:10px;">
            <button type="button" class="btn btn-secondary" onclick="closeShippingCard()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="submitShipping()">Submit</button>
        </div>
    </form>
</div>

<!-- Overlay -->
<div id="overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background: rgba(0,0,0,0.5); z-index: 9998;"></div>

<style>
/* optional: fade in effect */
#delivery-card.show {
    display: flex !important;
    animation: fadeIn 0.2s ease-in-out;
}
@keyframes fadeIn {
    from {opacity: 0;}
    to {opacity: 1;}
}
</style>

<script>
let currentOrderId = null; // store current order being updated

function handleStatusChange(select, orderId) {
    let status = select.value;

    if(status !== "shipping") {
        // Directly submit form if not shipping
        document.getElementById("delivery_" + orderId).value = '';
        document.getElementById("submit_" + orderId).click();
        return;
    }

    // Show shipping card
    currentOrderId = orderId;
    document.getElementById("shipping-card").style.display = "block";
    document.getElementById("overlay").style.display = "block";
}

// Close shipping card
function closeShippingCard() {
    document.getElementById("shipping-card").style.display = "none";
    document.getElementById("overlay").style.display = "none";
    if(currentOrderId) {
        // reset select back to pending
        document.querySelector('select[name="status"][onchange*="'+currentOrderId+'"]').value = 'pending';
        currentOrderId = null;
    }
}

// Submit shipping info
function submitShipping() {
    let deliveryService = document.getElementById("delivery_service_input").value.trim();
    let trackingNumber = document.getElementById("tracking_number_input").value.trim();

    if(!deliveryService) {
        alert("Delivery Service is required!");
        return;
    }

    // Set hidden input value
    document.getElementById("delivery_" + currentOrderId).value = deliveryService;

    // Optional: if you want to store tracking number as well
    let form = document.querySelector('form[action*="'+currentOrderId+'"]');
    if(form.querySelector('input[name="tracking_number"]') === null) {
        let tnInput = document.createElement("input");
        tnInput.type = "hidden";
        tnInput.name = "tracking_number";
        tnInput.value = trackingNumber;
        form.appendChild(tnInput);
    } else {
        form.querySelector('input[name="tracking_number"]').value = trackingNumber;
    }

    // Submit the form
    document.getElementById("submit_" + currentOrderId).click();

    // Close modal
    closeShippingCard();
}
</script>

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
