@extends('layouts.app')

@section('content')
<div class="content-box" style="max-width: 500px; margin: 0 auto;">
    <h3>➕ Add Product Variant</h3>
    <form action="{{ route('products.store') }}" method="POST">


        <div class="form-group">
            <label>Product Code</label>
            <input type="text" name="product_code" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Variant (other)</label>
            <input type="text" name="other" class="form-control" placeholder="chain, bracelet, etc.">
        </div>

        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Price</label>
            <input type="number" name="price" class="form-control" step="0.01" min="0" required>
        </div>

        <div class="form-group">
            <label>Stock</label>
            <input type="number" name="stock" class="form-control" min="0" required>
        </div>

        <button type="submit" class="btn btn-primary">Add Variant</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
