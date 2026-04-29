@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1><i class="fas fa-boxes"></i> Orders Management</h1>
    <a href="{{ route('orders.selectCustomer') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create Order
    </a>
</div>


<!-- FILTER FORM ALWAYS VISIBLE -->
<form method="GET" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom:30px;">
    <!-- Per Page -->
    <label for="per_page">Show</label>
    <select name="per_page" id="per_page" onchange="this.form.submit()" class="form-control form-control-sm">
        @foreach([5,10,20,50,100,'all'] as $size)
            <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }}</option>
        @endforeach
    </select>
    <span>orders per page</span>

    <!-- Date Filter -->
    <input type="date" name="date"
           value="{{ request('date') ?? now()->toDateString() }}"
           class="form-control">

    <!-- Status Filter -->
    <select name="status" class="form-control">
        <option value="all" {{ request('status')=='all' ? 'selected' : '' }}>All Status</option>
        <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
        <option value="shipping" {{ request('status')=='shipping' ? 'selected' : '' }}>Shipping</option>
        <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>Completed</option>
        <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>Rejected</option>
        <option value="out_of_stock" {{ request('status')=='out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
    </select>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary">Filter</button>
</form>
@if($orders->count() === 0)
    <div class="empty-state">
        <i class="fas fa-boxes" style="font-size:48px;"></i>
        <h3>No Orders Found for {{ request('date') ?? now()->toDateString() }}</h3>
        <p>Select another date or status above to try again.</p>
    </div>
@endif
@if($orders->count() > 0)
<button id="bulk_ship_btn" class="btn btn-success mb-3">
    Ship Selected Pending Orders
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

@if($orders instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="pagination-container" style="margin-top: 20px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
        <div class="pagination-links">
            {{ $orders->appends(request()->query())->links('vendor.pagination.custom') }}
        </div>
        <div class="pagination-summary" style="font-size: 14px; color: #555;">
            Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
        </div>
    </div>
@elseif($orders instanceof \Illuminate\Database\Eloquent\Collection && $orders->count() > 0)
    <div class="pagination-summary" style="margin-top: 20px; font-size: 14px; color: #555;">
        Showing all {{ $orders->count() }} orders
    </div>
@endif

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
                   <option value="royalexpress">Royal Express </option>
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
    console.log('Submitting bulk shipping');
    const selected = document.getElementById('selected_order_ids').value
        .split(',')
        .map(id => parseInt(id));

    if (selected.length === 0) {
        alert('Select at least one order');
        return;
    }

    const deliveryService = document.getElementById('delivery_service_input').value;

    if (deliveryService === 'transexpress') {
        // Transexpress bulk logic (already implemented)
        fetch('/api/orders/bulk-details', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ order_ids: selected })
        })
        .then(res => res.json())
        .then(orders => {
            const payload = orders.map(o => ({
                order_id: o.order_id,
                customer_name: o.customer_name,
                address: o.street_address,
                order_description: o.order_description,
                customer_phone: o.phone_number,
                customer_phone2: o.phone_number_2 ?? '',
                cod_amount: o.total_amount,
                city: o.city,
                remarks: o.remarks ?? '',
                delivery_service: deliveryService
            }));

            fetch('/api/orders/bulk-ship', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
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

    } else if (deliveryService === 'domestic') {
        // FDE Domestic: send single orders to backend
        fetch('/api/orders/bulk-ship-fde', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(selected.map(id => ({ order_id: id }))) // just array of order IDs
        })
        .then(res => res.json())
        .then(data => {
            if (data.results) {
                let successCount = data.results.filter(r => r.success).length;
                let failed = data.results.filter(r => !r.success);
                alert(`${successCount} orders shipped successfully.` +
                    (failed.length > 0 ? ` Failed: ${failed.map(f => `#${f.order_id}`).join(', ')}` : ''));
                location.reload();
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(err => { console.error(err); alert('FDE Domestic bulk shipping failed'); });
    }
    else if (deliveryService === 'royalexpress') {
        // FDE Domestic: send single orders to backend
        fetch('/api/orders/bulk-ship-royalexpress', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(selected.map(id => ({ order_id: id }))) // just array of order IDs
        })
        .then(res => res.json())
        .then(data => {
            if (data.results) {
                let successCount = data.results.filter(r => r.success).length;
                let failed = data.results.filter(r => !r.success);
                alert(`${successCount} orders shipped successfully.` +
                    (failed.length > 0 ? ` Failed: ${failed.map(f => `#${f.order_id}`).join(', ')}` : ''));
                location.reload();
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(err => { console.error(err); alert('FDE Domestic bulk shipping failed'); });
    }

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





/* --- Status Select --- */
.status-select {
    min-width: 120px;
    max-width: 160px;
    padding: 4px 8px;
    font-size: 0.85rem;
    border-radius: 4px;
    border: 1px solid #d1d5db;
    transition: all 0.2s;
}
.per-page-form {
    display: flex;
    gap: 8px;
    align-items: center; /* centers label, select, and span vertically */
}

.per-page-form select.form-control {
    width: auto; /* prevents select from taking full width */
    min-width: 60px;
}
.status-select:focus {
    outline: none;
    border-color: #2563eb;
}

/* --- Action Buttons --- */
.action-buttons a,
.action-buttons button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

/* --- Bulk Ship Button --- */
#bulk_ship_btn {
    margin-bottom: 15px;
}

/* --- Pagination --- */
.pagination-container {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

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
    font-weight: 500;
    transition: all 0.2s;
}

.pagination-links .page-item.active .page-link {
    background-color: #2563eb;
    color: #fff;
    border-color: #2563eb;
}

.pagination-links .page-item.disabled .page-link {
    color: #b0b0b0;
    cursor: not-allowed;
}

/* --- Per Page Form --- */
form[method="GET"] {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-bottom: 20px;
}

form[method="GET"] label {
    font-weight: 500;
}

form[method="GET"] select {
    min-width: 70px;
    border-radius: 4px;
    padding: 4px 8px;
}

/* --- Empty State --- */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.empty-state i {
    color: #9ca3af;
}

/* --- Shipping Modal --- */
#shipping-card {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    z-index: 9999;
    width: 100%;
    max-width: 380px;
}

#overlay {
    display: none;
    position: fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background: rgba(0,0,0,0.5);
    z-index: 9998;
}

/* --- Responsive --- */
@media (max-width: 768px) {
    .table th, .table td {
        font-size: 0.8rem;
        padding: 6px 8px;
    }

    form[method="GET"] {
        flex-direction: column;
        align-items: flex-start;
    }

    #shipping-card {
        width: 90%;
    }
}

@media (max-width: 768px) { .pagination-container { flex-direction: column; align-items: center; gap: 8px; } }
</style>


@endsection
