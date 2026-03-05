@extends('layouts.app')

@section('content')
<div class="content-box" style="max-width: 500px; margin: 0 auto;">
    <h3>➕ Add Product</h3>
    <form action="{{ route('products.store') }}" method="POST">
        @csrf
        <div class="form-group">
    <label>Product Code</label>
    <input type="text" name="product_code" class="form-control" required>
</div>

<div class="form-group">
    <label>Specifications (comma separated)</label>
    <input type="text" name="other" class="form-control" placeholder="chain,bracelet,ring">
</div>
        <div class="form-group">
            <label for="name">📝 Name</label>
            <input type="text" name="name" id="name" class="form-control" required value="{{ old('name') }}">
            @error('name')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="price">💲 Price</label>
            <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" required value="{{ old('price') }}">
            @error('price')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="stock">📦 Stock</label>
            <input type="number" name="stock" id="stock" class="form-control" min="0" required value="{{ old('stock') }}">
            @error('stock')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary">Save Product</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
