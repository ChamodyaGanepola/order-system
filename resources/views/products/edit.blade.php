@extends('layouts.app')

@section('content')
<div class="content-box" style="max-width: 500px; margin: 0 auto;">
    <h3>✏️ Edit Product Variant</h3>
    <form action="{{ route('products.update', $product) }}" method="POST"
        onsubmit="return confirm('Are you sure you want to update this product variant? ');">
@csrf
        @method('PUT')

        <div class="form-group">
            <label>Product Code</label>
            <input type="text" name="product_code" class="form-control" value="{{ $product->product_code }}" required>
        </div>

        

        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
        </div>

        <div class="form-group">
            <label>Price</label>
            <input type="number" name="price" class="form-control" step="0.01" min="0" value="{{ $product->price }}" required>
        </div>

        <div class="form-group">
            <label>Stock</label>
            <input type="number" name="stock" class="form-control" min="0" value="{{ $product->stock }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Update Variant</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
