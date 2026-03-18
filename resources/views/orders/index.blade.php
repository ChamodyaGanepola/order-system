@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1><i class="fas fa-boxes"></i> Orders Management</h1>
   <a href="{{ route('orders.selectCustomer') }}" class="btn btn-primary">
    <i class="fas fa-plus"></i> Create Order
</a>
</div>

@if($orders->count() > 0)
<table>
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Customer Address</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Waybill Number</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($orders as $order)
        <tr>
            <td><strong>#{{ $order->id }}</strong></td>
            <td>{{ $order->customer->full_name }}</td>
            <td>{{ $order->customer->street_address}}</td>
            <td><strong>Rs.{{ number_format($order->total_amount, 2) }}</strong></td>
            <td>
                @if(in_array($order->status, ['pending', 'shipping']))
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
                @if($order->waybill_number)
                    <a href="https://portal.transexpress.lk/track/{{ $order->waybill_number }}" target="_blank">
                        {{ $order->waybill_number }}
                    </a>
                @else
                    -
                @endif
            </td>
           <td>
    <div class="action-buttons" style="display:flex; gap:4px; justify-content:center;">
        @if(in_array($order->status, ['pending', 'shipping', 'out_of_stock']))
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

<!-- Shipping Info Modal -->
<div id="shipping-card" style="display:none; position: fixed; top: 50%; left: 50%;
    transform: translate(-50%, -50%); background: white; padding: 20px;
    border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); z-index: 9999; width: 350px;">
    <h4>Shipping Details</h4>
    <form id="shipping-form">
        <div class="form-group" style="margin-bottom:10px;">
            <label for="delivery_service">Delivery Service</label>
            <select id="delivery_service_input" class="form-control">
    <option value="transexpress" selected>Trans Express (Sri Lanka)</option>
    <option value="domestic">Fadar Express / Domestic</option>
</select>
        </div>

        <!-- Trans Express fields -->
        <div id="transex_fields" style="display:none;">
            <div class="form-group">
                <label for="province">Province</label>
                <select id="province_select" class="form-control">
                    <option value="">Select Province</option>
                </select>
            </div>
            <div class="form-group">
                <label for="district">District</label>
                <select id="district_select" class="form-control" disabled>
                    <option value="">Select District</option>
                </select>
            </div>
            <div class="form-group">
                <label for="city">City</label>
                <select id="city_select" class="form-control" disabled>
                    <option value="">Select City</option>
                </select>
            </div>
        </div>

        <!-- Domestic fields -->
        <div id="domestic_fields" style="display:none;">
            <div class="form-group">
                <label for="province_domestic">Province</label>
                <select id="province_domestic" class="form-control">
                    <option value="">Select Province</option>
                    <option value="Western">Western</option>
                    <option value="Central">Central</option>
                    <option value="Southern">Southern</option>
                    <option value="Northern">Northern</option>
                    <option value="Eastern">Eastern</option>
                    <option value="North Western">North Western</option>
                    <option value="North Central">North Central</option>
                    <option value="Uva">Uva</option>
                    <option value="Sabaragamuwa">Sabaragamuwa</option>
                </select>
            </div>
            <div class="form-group">
                <label for="city_domestic">City</label>
                <select id="city_domestic" class="form-control">
    <option value="Colombo" selected>Colombo</option>
    <option value="Kandy">Kandy</option>
    <option value="Galle">Galle</option>
    <option value="Jaffna">Jaffna</option>
    <option value="Kurunegala">Kurunegala</option>
    <option value="Anuradhapura">Anuradhapura</option>
    <option value="Matale">Matale</option>
    <option value="Matara">Matara</option>
</select>
            </div>
        </div>
 <div class="form-group">
        <label>Parcel Weight (kg)</label>
        <input type="number" id="parcel_weight" class="form-control" value="1" min="1">
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

<script>
let currentOrderId = null;

function handleStatusChange(select, orderId) {
    const status = select.value;

    if (status !== 'shipping') {
        // Update immediately if not shipping
        updateStatusApi(orderId, status, '', '');
        return;
    }

    // If shipping, just open modal
    currentOrderId = orderId;
    document.getElementById('shipping-card').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}

function closeShippingCard() {
    document.getElementById('shipping-card').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';

    // Reset the dropdown to pending if modal closed without submitting
    if (currentOrderId) {
        const select = document.querySelector(`select[name="status"][onchange*="${currentOrderId}"]`);
        if(select) select.value = 'pending';
        currentOrderId = null;
    }
}

function submitShipping() {
    const deliveryService = document.getElementById('delivery_service_input').value;

    const city = deliveryService === 'transexpress'
        ? document.getElementById('city_select').value
        : document.getElementById('city_domestic').value;

    const weight = document.getElementById('parcel_weight').value || 1;

    if (!deliveryService) {
        alert('Select delivery service');
        return;
    }

    if (!city) {
        alert('Select a city');
        return;
    }

    const data = {
        status: 'shipping',
        delivery_service: deliveryService,
        city: city,
        weight: weight
    };

    console.log("Sending order ID:", currentOrderId);

    fetch('/api/orders/' + currentOrderId + '/update-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        console.log(data);

        if (data.success) {
            alert(data.success);
            location.reload();
        } else if (data.error) {
            alert(data.error);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Failed to update status.');
    });

    closeShippingCard();
}
// API call
function updateStatusApi(orderId,status,deliveryService,city){
    fetch('/api/orders/'+orderId+'/update-status',{
        method:'POST',
        headers:{'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({status, delivery_service: deliveryService, city})
    }).then(res=>res.json()).then(data=>{
        if(data.success){ alert(data.success); location.reload(); }
        else if(data.error) alert(data.error);
    }).catch(err=>{ console.error(err); alert('Failed to update status.'); });
}

// Delivery service selection
// Show/hide fields on page load based on default selected value
window.addEventListener('DOMContentLoaded', () => {
    const val = document.getElementById('delivery_service_input').value;
    document.getElementById('transex_fields').style.display = val==='transexpress' ? 'block' : 'none';
    document.getElementById('domestic_fields').style.display = val==='domestic' ? 'block' : 'none';

    // Show/hide weight field
    document.getElementById('parcel_weight').parentElement.style.display = val==='domestic' ? 'block' : 'none';

    if(val==='transexpress') loadTransexProvinces();
});

document.getElementById('delivery_service_input').addEventListener('change', function() {
    const val = this.value;
    document.getElementById('transex_fields').style.display = val==='transexpress' ? 'block' : 'none';
    document.getElementById('domestic_fields').style.display = val==='domestic' ? 'block' : 'none';
    document.getElementById('parcel_weight').parentElement.style.display = val==='domestic' ? 'block' : 'none';
});
// Trans Express provinces/districts
function loadTransexProvinces(){
    const provinceSelect = document.getElementById('province_select');
    const districtSelect = document.getElementById('district_select');
    const citySelect = document.getElementById('city_select');

    // Clear previous options
    provinceSelect.innerHTML = '';
    districtSelect.innerHTML = '';
    citySelect.innerHTML = '';
    districtSelect.disabled = true;
    citySelect.disabled = true;

    // Fetch provinces
    fetch('https://portal.transexpress.lk/api/provinces', { headers: { 'Accept':'application/json' } })
    .then(res => res.json())
    .then(provinces => {
        provinces.forEach((p,i) => {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = p.text;
            provinceSelect.appendChild(opt);
        });
        // Auto-select first province
        provinceSelect.selectedIndex = 0;
        loadDistricts(provinceSelect.value);
    });

    // Province change
    provinceSelect.addEventListener('change', ()=>loadDistricts(provinceSelect.value));

    function loadDistricts(provinceId){
        districtSelect.innerHTML = '';
        citySelect.innerHTML = '';
        districtSelect.disabled = true;
        citySelect.disabled = true;
        if(!provinceId) return;

        fetch(`https://portal.transexpress.lk/api/districts?province_id=${provinceId}`, { headers:{'Accept':'application/json'} })
        .then(res=>res.json())
        .then(districts=>{
            districts.forEach(d=>{
                const opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = d.text;
                districtSelect.appendChild(opt);
            });
            // Auto-select first district
            districtSelect.selectedIndex = 0;
            loadCities(districtSelect.value);
            districtSelect.disabled = false;
        });
    }

    // District change
    districtSelect.addEventListener('change', ()=>loadCities(districtSelect.value));

    function loadCities(districtId){
        citySelect.innerHTML = '';
        citySelect.disabled = true;
        if(!districtId) return;

        fetch(`https://portal.transexpress.lk/api/cities?district_id=${districtId}`, { headers:{'Accept':'application/json'} })
        .then(res=>res.json())
        .then(cities=>{
            cities.forEach(c=>{
                const opt = document.createElement('option');
                opt.value = c.text;
                opt.textContent = c.text;
                citySelect.appendChild(opt);
            });
            // Auto-select first city
            if(cities.length>0) citySelect.selectedIndex = 0;
            citySelect.disabled = false;
        });
    }
}



</script>

<style>
/* Make status dropdown fit the table cell */
td select[name="status"] {
    width: 100%;        /* take full width of the cell */
    max-width: 120px;   /* optional: prevent too wide */
    padding: 2px 6px;
    font-size: 13px;
    border-radius: 4px;
}

td.status-column select {
    min-width: 120px;
}
/* Apply only once */
.status-select {
    min-width: 120px;   /* enough for 'Pending', 'Shipping', etc. */
    max-width: 150px;   /* optional: prevent huge stretch */
    padding: 2px 6px;
    font-size: 0.85rem; /* readable font */
    border-radius: 4px;
}
.pagination-links .pagination { display: flex; flex-wrap: wrap; gap: 6px; margin: 0; padding: 0; list-style: none; }
.pagination-links .page-item .page-link { padding: 6px 12px; border-radius: 6px; border: 1px solid #e2e8f0; color: #1e293b; transition: all 0.2s; }
.pagination-links .page-item.active .page-link { background-color: #2563eb; color: white; border-color: #2563eb; }
.pagination-links .page-item.disabled .page-link { color: #b0b0b0; cursor: not-allowed; }
@media (max-width: 768px) { .pagination-container { flex-direction: column; align-items: center; gap: 8px; } }
</style>

@else
<div class="empty-state">
    <i class="fas fa-boxes"></i>
    <h3>No Orders Found</h3>
    <p>Create your first order by selecting a customer</p>
</div>
@endif
@endsection
