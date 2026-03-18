@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1><i class="fas fa-edit"></i> Edit Order #{{ $order->id }}</h1>
    <div style="background: #f0f9ff; padding: 10px 20px; border-radius: 6px; border-left: 4px solid #2563eb;">
        <strong>Customer:</strong> {{ $order->customer->full_name }}
    </div>
</div>

<div class="content-box">
    <form action="{{ route('orders.update', $order) }}" method="POST"
         onsubmit="return confirm('Are you sure you want to edit this order? ');">
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
                    <td>{{ $item->product->name }} ({{ $item->product->product_code }}) - Variant: {{ $item->product->other ?? 'N/A' }}</td>
                    <td>Rs. {{ number_format($item->price, 2) }}</td>
                    <td>
                        <input type="number" name="products[{{ $item->product_id }}]"
                               value="{{ $item->quantity }}"
                               min="1"
                               max="{{ $item->product->stock > 0 ? $item->product->stock : $item->quantity }}">
                    </td>
                    <td>Rs. {{ number_format($item->subtotal, 2) }}</td>
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
                    <option value="{{ $product->id }}"
                            data-price="{{ $product->price }}"
                            data-stock="{{ $product->stock }}"
                            {{ $product->stock <= 0 ? 'disabled' : '' }}>
                        {{ $product->name }} ({{ $product->product_code }}) - Variant: {{ $product->other ?? 'N/A' }} (Rs. {{ number_format($product->price,2) }}) - Stock: {{ $product->stock }}
                    </option>
                @endforeach
            </select>
            <input type="number" id="add-product-quantity" placeholder="Quantity" min="1" style="width: 100px;">
            <button type="button" class="btn btn-success btn-sm" id="add-product-btn">Add Product</button>
        </div>

        <div class="btn-group" style="margin-top: 15px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle"></i> Update Order</button>
            <a href="{{ route('orders.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
        </div>
    </form>
</div>

<script>
document.getElementById('add-product-btn').addEventListener('click', function() {
    const select = document.getElementById('add-product-select');
    const quantityInput = document.getElementById('add-product-quantity');
    const productId = select.value;
    const productName = select.options[select.selectedIndex].text;
    const price = parseFloat(select.options[select.selectedIndex].dataset.price);
    const stock = parseInt(select.options[select.selectedIndex].dataset.stock);
    const quantity = parseInt(quantityInput.value);

    if (!productId) { alert('Please select a product'); return; }
    if (!quantity || quantity < 1) { alert('Enter valid quantity'); return; }
    if (quantity > stock) { alert('Quantity exceeds stock'); return; }

    // Prevent duplicate rows
    if(document.querySelector('#order-items-table tbody tr[data-product-id="'+productId+'"]')){
        alert('Product already added!');
        return;
    }

    const tbody = document.querySelector('#order-items-table tbody');
    const row = document.createElement('tr');
    row.setAttribute('data-product-id', productId);
    row.innerHTML = `
    <td>${productName}</td>
    <td>Rs. ${price.toFixed(2)}</td>
    <td><input type="number" name="products[${productId}]" value="${quantity}" min="1" max="${stock}"></td>
    <td>Rs. ${(price*quantity).toFixed(2)}</td>
    <td><button type="button" class="btn btn-danger btn-sm remove-item">Remove</button></td>
`;
    tbody.appendChild(row);

    select.value = '';
    quantityInput.value = '';
});

// Remove row
document.addEventListener('click', function(e){
    if(e.target.classList.contains('remove-item')){
        e.target.closest('tr').remove();
    }
});
</script>
@endsection
