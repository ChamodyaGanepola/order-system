@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1><i class="fas fa-edit"></i> Edit Order #{{ $order->id }}</h1>
    <div style="background: #f0f9ff; padding: 10px 20px; border-radius: 6px; border-left: 4px solid #2563eb;">
        <strong>Customer:</strong> {{ $order->customer->full_name }}
    </div>
</div>

<div class="content-box">
    <form action="{{ route('orders.update', $order) }}" method="POST">
        @csrf
        @method('PUT')

        <h3><i class="fas fa-box"></i> Order Items</h3>

        <table id="order-items-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr data-product-id="{{ $item->product_id }}">
                    <td>{{ $item->product->name }}</td>
                    <td>${{ number_format($item->price, 2) }}</td>
                    <td>
                        <input type="number" name="products[{{ $item->product_id }}]" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock }}">
                    </td>
                    <td>${{ number_format($item->subtotal, 2) }}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 15px;">
            <select id="add-product-select">
                <option value="">-- Select Product to Add --</option>
                @foreach(App\Models\Product::all() as $product)
                    <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->name }} (${{ number_format($product->price, 2) }})</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-success btn-sm" id="add-product-btn">Add Product</button>
        </div>

        <div class="btn-group" style="margin-top: 15px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle"></i> Update Order</button>
            <a href="/orders" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
        </div>
    </form>
</div>

<script>
document.getElementById('add-product-btn').addEventListener('click', function() {
    const select = document.getElementById('add-product-select');
    const productId = select.value;
    const productName = select.options[select.selectedIndex].text;
    const price = select.options[select.selectedIndex].dataset.price;

    if (!productId) return;

    // Prevent adding duplicate products
    if(document.querySelector('#order-items-table tbody tr[data-product-id="'+productId+'"]')) {
        alert('Product already added!');
        return;
    }

    const tbody = document.querySelector('#order-items-table tbody');
    const row = document.createElement('tr');
    row.setAttribute('data-product-id', productId);
    row.innerHTML = `
        <td>${productName}</td>
        <td>$${parseFloat(price).toFixed(2)}</td>
        <td><input type="number" name="products[${productId}]" value="1" min="1"></td>
        <td>$${parseFloat(price).toFixed(2)}</td>
        <td><button type="button" class="btn btn-danger btn-sm remove-item">Remove</button></td>
    `;
    tbody.appendChild(row);
});

// Remove product row
document.addEventListener('click', function(e){
    if(e.target.classList.contains('remove-item')){
        e.target.closest('tr').remove();
    }
});
</script>
@endsection
