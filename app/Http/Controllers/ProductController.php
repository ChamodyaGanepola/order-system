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
            'name'         => 'required|string|max:255',
            'product_code' => 'required|string|max:100|unique:products,product_code',
            'price'        => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
        ]);

        Product::create([
            'name'         => $request->name,
            'product_code' => $request->product_code,
            'price'        => $request->price,
            'stock'        => $request->stock,
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
            'name'         => 'required|string|max:255',
            'product_code' => 'required|string|max:100|unique:products,product_code,' . $product->id,
            'price'        => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
        ]);

        $product->update([
            'name'         => $request->name,
            'product_code' => $request->product_code,
            'price'        => $request->price,
            'stock'        => $request->stock,
        ]);

        // Auto-check out-of-stock orders
        $this->updatePendingOrders($product);

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    /**
     * Convert out_of_stock orders → pending if stock is now enough
     */
    protected function updatePendingOrders(Product $product)
    {
        $orders = \App\Models\Order::where('status', 'out_of_stock')
            ->whereHas('items', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })
            ->with('items')
            ->get();

        foreach ($orders as $order) {
            $canFulfill = true;

            foreach ($order->items as $item) {
                $itemProduct = $item->product;
                if (! $itemProduct) continue;

                if ($itemProduct->stock < $item->quantity) {
                    $canFulfill = false;
                    break;
                }
            }

            if ($canFulfill) {
                foreach ($order->items as $item) {
                    $itemProduct = $item->product;
                    if (! $itemProduct) continue;

                    $itemProduct->stock -= $item->quantity;
                    $itemProduct->save();
                }

                $order->status = 'pending';
                $order->save();
            }
        }
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
    }
}
