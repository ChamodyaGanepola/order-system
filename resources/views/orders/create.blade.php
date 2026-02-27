@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1><i class="fas fa-plus-circle"></i> Create Order</h1>
    <div style="background: #f0f9ff; padding: 10px 20px; border-radius: 6px; border-left: 4px solid #2563eb;">
        <strong>Customer:</strong> {{ $customer->full_name }}
    </div>
</div>

<div class="content-box">
    <form action="/orders" method="POST">
        @csrf
        <input type="hidden" name="customer_id" value="{{ $customer->id }}">

        <h3><i class="fas fa-box"></i> Select Products and Quantities</h3>

        @if($products->count() > 0)
        <table>
            <thead>
                <tr>
                    <th><i class="fas fa-cube"></i> Product Name</th>
                    <th><i class="fas fa-money-bill-wave"></i> Price</th>
                    <th><i class="fas fa-warehouse"></i> Stock</th>
                    <th><i class="fas fa-shopping-cart"></i> Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td><strong>{{ $product->name }}</strong></td>
                    <td>${{ number_format($product->price, 2) }}</td>
                    <td>
                        @if($product->stock > 0)
                            <span style="background: #ecfdf5; color: #166534; padding: 4px 8px; border-radius: 4px; font-weight: 600;">{{ $product->stock }}</span>
                        @else
                            <span style="background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-weight: 600;">Out of Stock</span>
                        @endif
                    </td>
                    <td>
                        <input type="number" name="products[{{ $product->id }}]" value="0" min="0" max="{{ $product->stock }}" style="width: 100px; padding: 8px; border: 2px solid #e2e8f0; border-radius: 4px;">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-state">
            <i class="fas fa-cube"></i>
            <h3>No Products Available</h3>
            <p>Please add products to the system first</p>
        </div>
        @endif

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check-circle"></i> Create Order
            </button>
            <a href="/customers" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Cancel
            </a>
        </div>
    </form>
</div>
@endsection
