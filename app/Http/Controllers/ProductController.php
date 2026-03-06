<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('created_at', 'desc')->paginate(10);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'product_code' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'other' => 'nullable|string'
        ]);

        $variant = $request->other ?? 'N/A';

        $exists = Product::where('product_code', $request->product_code)
                         ->where('other', $variant)
                         ->exists();

        if ($exists) {
            return back()->withErrors([
                'other' => "This variant '{$variant}' for product code {$request->product_code} already exists."
            ]);
        }

        Product::create([
            'name' => $request->name,
            'product_code' => $request->product_code,
            'price' => $request->price,
            'stock' => $request->stock,
            'other' => $variant
        ]);

        return redirect()->route('products.index')->with('success', 'Product added successfully!');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'product_code' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'other' => 'nullable|string'
        ]);

        $variant = $request->other ?? 'N/A';

        $exists = Product::where('product_code', $request->product_code)
                         ->where('other', $variant)
                         ->where('id', '!=', $product->id)
                         ->exists();

        if ($exists) {
            return back()->withErrors([
                'other' => "This variant '{$variant}' for product code {$request->product_code} already exists."
            ]);
        }

        $product->update([
            'name' => $request->name,
            'product_code' => $request->product_code,
            'price' => $request->price,
            'stock' => $request->stock,
            'other' => $variant
        ]);

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
    }
}
