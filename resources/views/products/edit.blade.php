@extends('layouts.app')

@section('content')
<div class="content-box" style="max-width: 500px; margin: 0 auto;">
    <h3>✏️ Edit Product</h3>
    <form action="{{ route('products.update', $product) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">📝 Name</label>
            <input type="text" name="name" id="name" class="form-control" required value="{{ old('name', $product->name) }}">
            @error('name')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="price">💲 Price</label>
            <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" required value="{{ old('price', $product->price) }}">
            @error('price')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="stock">📦 Stock</label>
            <input type="number" name="stock" id="stock" class="form-control" min="0" required value="{{ old('stock', $product->stock) }}">
            @error('stock')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary">Update Product</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
