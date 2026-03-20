@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1><i class="fas fa-boxes"></i> Orders Management</h1>
    <a href="{{ route('orders.selectCustomer') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create Order
    </a>
</div>

@if($orders->count() > 0)
<button id="bulk_ship_btn" class="btn btn-success mb-3">
    Ship Selected Orders (Tran Express)
</button>

<table class="table table-bordered">
    <thead>
        <tr>
           <th>
    @if($orders->where('status', 'pending')->count() > 0)
        <input type="checkbox" id="select_all">
    @endif
    Order ID
</th>
            <th>Customer</th>
            <th>Customer Address</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Status Date</th>
            <th>Waybill Number</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($orders as $order)
        <tr>
            <td>
    @if($order->status === 'pending')
        <input type="checkbox" class="order-checkbox" value="{{ $order->id }}">
    @endif
    <strong>#{{ $order->id }}</strong>
</td>
            <td>{{ $order->customer->full_name }}</td>
            <td>{{ $order->customer->street_address }}</td>
            <td><strong>Rs.{{ number_format($order->total_amount, 2) }}</strong></td>
            <td>
                @if(in_array($order->status, ['pending', 'shipping']))
                <select name="status" onchange="handleStatusChange(this, '{{ $order->id }}')" class="status-select">
                    <option value="{{ $order->status }}" selected disabled>{{ ucfirst($order->status) }}</option>
                    @if($order->status === 'pending')
                        <option value="shipping">Shipping</option>
                        <option value="rejected">Rejected</option>
                    @elseif($order->status === 'shipping')
                        <option value="completed">Completed</option>
                        <option value="rejected">Rejected</option>
                    @elseif($order->status === 'out_of_stock')
                        <option value="pending">Pending</option>
                    @endif
                </select>
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
            <td>{{ $order->status_date ? \Carbon\Carbon::parse($order->status_date)->format('d M Y H:i') : '-' }}</td>
            <td>{{ $order->waybill_number ?? '-' }}</td>
            <td>
                <div class="action-buttons" style="display:flex; gap:4px; justify-content:center;">
                    @if(in_array($order->status, ['pending',  'out_of_stock']))
                    <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-secondary btn-sm" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    @endif
                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-primary btn-sm" title="View Order">
                        <i class="fas fa-eye"></i>
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

<!-- Shipping Modal -->
<div id="shipping-card" style="display:none; position: fixed; top: 50%; left: 50%;
    transform: translate(-50%, -50%); background: white; padding: 20px;
    border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); z-index: 9999; width: 350px;">

    <h4>Shipping Details</h4>
    <form id="shipping-form">
        <input type="hidden" id="selected_order_ids">

        <div class="form-group" style="margin-bottom:10px;">
            <label for="delivery_service_input">Delivery Service</label>
            <select id="delivery_service_input" class="form-control">
                <option value="transexpress" selected>Transexpress (Sri Lanka)</option>
                <option value="domestic">Fadar Express / Domestic</option>
            </select>
        </div>

        <div style="display:flex; justify-content: flex-end; gap:10px; margin-top: 10px;">
            <button type="button" class="btn btn-secondary" onclick="closeShippingCard()">Cancel</button>
            <button type="button" id="submitSingleBtn" class="btn btn-primary" onclick="submitShippingSingle()">Submit</button>
            <button type="button" id="submitBulkBtn" class="btn btn-primary" onclick="submitShippingBulk()">Submit Bulk</button>
        </div>
    </form>
</div>

<div id="overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background: rgba(0,0,0,0.5); z-index: 9998;"></div>

<script>
    const selectAll = document.getElementById('select_all');
if (selectAll) {
    selectAll.addEventListener('change', function() {
        const checked = this.checked;
        document.querySelectorAll('.order-checkbox').forEach(cb => {
            // only enable pending checkboxes
            if (!cb.disabled) cb.checked = checked;
        });
    });
}
let currentOrderId = null;

document.addEventListener('DOMContentLoaded', function() {
    // Bulk Ship Button
    const bulkShipBtn = document.getElementById('bulk_ship_btn');
    if(bulkShipBtn){
        bulkShipBtn.addEventListener('click', function() {
            const selected = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
            if(selected.length === 0){ alert('Select at least one order'); return; }

            currentOrderId = null;
            document.getElementById('selected_order_ids').value = selected.join(',');
            document.getElementById('shipping-card').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';

            document.getElementById('submitBulkBtn').style.display = 'block';
            document.getElementById('submitSingleBtn').style.display = 'none';
        });
    }

    // Select all checkboxes
    const selectAll = document.getElementById('select_all');
    if(selectAll){
        selectAll.addEventListener('change', function() {
            const checked = this.checked;
            document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = checked);
        });
    }
});

function closeShippingCard() {
    document.getElementById('shipping-card').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
    currentOrderId = null;
}

function handleStatusChange(select, orderId) {
    const status = select.value;
    if(status !== 'shipping'){ updateStatusApi(orderId, status); return; }

    currentOrderId = orderId;
    document.getElementById('shipping-card').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';

    document.getElementById('submitSingleBtn').style.display = 'block';
    document.getElementById('submitBulkBtn').style.display = 'none';
}

function submitShippingSingle() {
    if(!currentOrderId) return;

    const deliveryService = document.getElementById('delivery_service_input').value;
    fetch('/api/orders/' + currentOrderId + '/update-status', {
        method:'POST',
        headers:{'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({status:'shipping', delivery_service: deliveryService})
    })
    .then(res => res.json())
    .then(data => { if(data.success){ alert(data.success); location.reload(); } else if(data.error) alert(data.error); })
    .catch(err => { console.error(err); alert('Failed to update status'); });

    closeShippingCard();
}

function submitShippingBulk() {
    const selected = document.getElementById('selected_order_ids').value
        .split(',')
        .map(id => parseInt(id));

    if (selected.length === 0) {
        alert('Select at least one order');
        return;
    }

    // Step 1: Fetch order details from backend
    fetch('/api/orders/bulk-details', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ order_ids: selected })
    })
    .then(res => res.json())
    .then(orders => {

        // Step 2: Map orders to exact Transex format
        const payload = orders.map(o => ({
            order_id: o.order_id,
            customer_name: o.customer_name,
            address: o.street_address,
            order_description: o.order_description, // use existing
            customer_phone: o.phone_number,
            customer_phone2: o.phone_number_2 ?? '',
            cod_amount: o.total_amount,
            city: o.city,
            remarks: o.remarks ?? ''
        }));

        // Step 3: Send raw array directly (no wrapper)
        fetch('/api/orders/bulk-ship', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload) // ✅ raw array
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.success);
                location.reload();
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(err => { console.error(err); alert('Bulk shipping failed'); });
    })
    .catch(err => { console.error(err); alert('Failed to fetch order details'); });

    closeShippingCard();
}

function updateStatusApi(orderId, status){
    fetch('/api/orders/' + orderId + '/update-status', {
        method:'POST',
        headers:{'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({status})
    })
    .then(res=>res.json())
    .then(data => { if(data.success){ alert(data.success); location.reload(); } else if(data.error) alert(data.error); })
    .catch(err=>{ console.error(err); alert('Failed to update status.'); });
}
</script>

<style>
.status-select { min-width: 120px; max-width: 150px; padding: 2px 6px; font-size: 0.85rem; border-radius: 4px; }
.pagination-links .pagination { display: flex; flex-wrap: wrap; gap: 6px; margin: 0; padding: 0; list-style: none; }
.pagination-links .page-item .page-link { padding: 6px 12px; border-radius: 6px; border: 1px solid #e2e8f0; color: #1e293b; transition: all 0.2s; }
.pagination-links .page-item.active .page-link { background-color: #2563eb; color: white; border-color: #2563eb; }
.pagination-links .page-item.disabled .page-link { color: #b0b0b0; cursor: not-allowed; }
@media (max-width: 768px) { .pagination-container { flex-direction: column; align-items: center; gap: 8px; } }
</style>

@else
<div class="empty-state text-center">
    <i class="fas fa-boxes" style="font-size:48px"></i>
    <h3>No Orders Found</h3>
    <p>Create your first order by selecting a customer</p>
</div>
@endif
@endsection
