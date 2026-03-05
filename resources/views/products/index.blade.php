@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <h1 style="display: flex; align-items: center; gap: 8px; color: var(--primary); font-weight: 700; font-size: 24px;">
        <i class="fas fa-box"></i> Products
    </h1>
    <a href="{{ route('products.create') }}" class="btn btn-primary" style="display: flex; align-items: center; gap: 6px;">
        <i class="fas fa-plus"></i> Add Product
    </a>
</div>

@if(session('success'))
    <div id="success-alert" class="alert alert-success">
        {{ session('success') }}
    </div>

    <script>
        setTimeout(function () {
            let alert = document.getElementById('success-alert');
            if (alert) {
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 500);
            }
        }, 3000);
    </script>
@endif

@if($products->count())
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Code</th>
<th>Specs</th>
                <th>Name</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product->product_code }}</td>
<td>{{ $product->other ? implode(',', $product->other) : '' }}</td>
                <td>{{ $product->name }}</td>
                <td>${{ number_format($product->price, 2) }}</td>
                <td>{{ $product->stock }}</td>
                <td style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 4px;">
                        <i class="fas fa-edit fa-sm"></i> Edit
                    </a>
                    <form action="{{ route('products.destroy', $product) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" style="display: flex; align-items: center; gap: 4px;" onclick="return confirm('Delete this product?')">
                            <i class="fas fa-trash fa-sm"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-center mt-3">
        {{ $products->links('pagination::bootstrap-5') }}
    </div>
@else
    <div class="empty-state">
        <h3>No products found.</h3>
    </div>
@endif
@endsection
