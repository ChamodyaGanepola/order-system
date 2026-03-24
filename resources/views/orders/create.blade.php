@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1><i class="fas fa-plus-circle"></i> {{ isset($order) ? 'Edit Order' : 'Create Order' }}</h1>
    <div style="background: #f0f9ff; padding: 10px 20px; border-radius: 6px; border-left: 4px solid #2563eb;">
        <strong>Customer:</strong> {{ $customer->full_name }}
    </div>
</div>

<div class="content-box">
<form id="order-form"
      action="{{ isset($order) ? route('orders.update', $order->id) : url('/orders') }}"
      method="POST">
@csrf
@if(isset($order)) @method('PUT') @endif

<input type="hidden" name="customer_id" value="{{ $customer->id }}">

<h3><i class="fas fa-box"></i> Order Items</h3>

<table id="order-items-table" class="table table-striped">
    <thead>
        <tr>
            <th>Product</th>
            <th>Code</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($order))
            @foreach($order->items as $item)
                <tr data-product-id="{{ $item->product_id }}">
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->product->product_code }}</td>
                    <td>Rs. {{ number_format($item->price,2) }}</td>
                    <td>
                        <input type="number" name="products[{{ $item->product_id }}]" value="{{ $item->quantity }}" min="1">
                    </td>
                    <td>Rs. {{ number_format($item->subtotal,2) }}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                    </td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>

<div style="margin-top: 15px; display: flex; gap: 10px; align-items: center;">
    <select id="add-product-select">
        <option value="">-- Select Product --</option>
        @foreach(\App\Models\Product::all() as $product)
            <option
                value="{{ $product->id }}"
                data-price="{{ $product->price }}"
                data-stock="{{ $product->stock }}"
                data-code="{{ $product->product_code }}"
                {{ $product->stock <= 0 ? 'disabled' : '' }}
            >
                {{ $product->name }} (Code: {{ $product->product_code }})
                - Rs.{{ number_format($product->price,2) }}, Stock: {{ $product->stock }}
                @if($product->stock <= 0) - Out of Stock @endif
            </option>
        @endforeach
    </select>

    <input type="number" id="add-product-quantity" placeholder="Quantity" min="1" style="width: 100px;">
    <button type="button" class="btn btn-success btn-sm" id="add-product-btn">Add Product</button>
</div>

<div class="btn-group" style="margin-top: 15px;">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-check-circle"></i> {{ isset($order) ? 'Update Order' : 'Create Order' }}
    </button>
    <a href="/orders" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Cancel
    </a>
</div>
</form>
</div>

<script>
// Submit validation
document.getElementById('order-form').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('#order-items-table tbody tr');

    if (rows.length === 0) {
        alert('Please add at least one product.');
        e.preventDefault();
        return;
    }

    let valid = true;

    rows.forEach(row => {
        const qty = parseInt(row.querySelector('input').value);
        if (!qty || qty < 1) valid = false;
    });

    if (!valid) {
        alert('Invalid quantity!');
        e.preventDefault();
    }
});

// Add product
document.getElementById('add-product-btn').addEventListener('click', function() {
    const select = document.getElementById('add-product-select');
    const quantityInput = document.getElementById('add-product-quantity');

    const productId = select.value;
    const productName = select.options[select.selectedIndex].text.split(' (')[0];
    const price = parseFloat(select.options[select.selectedIndex].dataset.price);
    const stock = parseInt(select.options[select.selectedIndex].dataset.stock);
    const productCode = select.options[select.selectedIndex].dataset.code;
    const quantity = parseInt(quantityInput.value);

    if (!productId) return alert('Select product');
    if (!quantity || quantity < 1) return alert('Enter valid quantity');
    if (quantity > stock) return alert('Exceeds stock');

    if (document.querySelector(`[data-product-id="${productId}"]`)) {
        return alert('Already added');
    }

    const row = document.createElement('tr');
    row.setAttribute('data-product-id', productId);

    row.innerHTML = `
        <td>${productName}</td>
        <td>${productCode}</td>
        <td>Rs. ${price.toFixed(2)}</td>
        <td><input type="number" name="products[${productId}]" value="${quantity}" min="1"></td>
        <td>Rs. ${(price * quantity).toFixed(2)}</td>
        <td><button type="button" class="btn btn-danger btn-sm remove-item">Remove</button></td>
    `;

    document.querySelector('#order-items-table tbody').appendChild(row);

    select.value = '';
    quantityInput.value = '';
});

// Remove product
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-item')) {
        e.target.closest('tr').remove();
    }
});
</script>
@endsection
