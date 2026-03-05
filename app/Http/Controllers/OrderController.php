<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Show form to create order for a customer
    public function create(Customer $customer)
    {
        $products = Product::all();
        return view('orders.create', compact('customer', 'products'));
    }

    // Store order
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'products'    => 'required|array',
        ]);

        $order = Order::create([
            'customer_id'  => $request->customer_id,
            'total_amount' => 0,
            'status'       => 'pending',
        ]);

        $total = 0;

        foreach ($request->products as $productId => $quantity) {
            if ($quantity <= 0) {
                continue;
            }
            // Skip if quantity is zero

            $product = Product::find($productId);

            if (! $product) {
                continue;
            }
            // Skip if product not found

            $subtotal = $product->price * $quantity;

            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $product->id,
                'quantity'   => $quantity,
                'price'      => $product->price,
                'subtotal'   => $subtotal,
            ]);

            $total += $subtotal;

            // Optionally, reduce product stock
            $product->stock -= $quantity;
            $product->save();
        }

        $order->total_amount = $total;
        $order->save();

        return redirect('/orders/pending')->with('success', 'Order created successfully!');
    }

    // Optional: show all pending orders
    public function pending(Request $request)
    {
        $perPage = $request->input('per_page', 10); // default 10 per page
        $orders  = Order::where('status', 'pending')
            ->with('customer')
            ->paginate($perPage);
        return view('orders.pending', compact('orders'));
    }
    // Show all orders
    public function index()
    {
        $orders = Order::with('customer')->get();
        return view('orders.index', compact('orders'));
    }

    // Show shipping orders
    public function shipping()
    {
        $orders = Order::where('status', 'shipping')->with('customer')->get();
        return view('orders.shipping', compact('orders'));
    }
    public function show($id)
    {
        $order = Order::with('customer', 'items.product')->findOrFail($id);

        return view('orders.show', compact('order'));
    }

    // Show rejected orders
    public function rejected()
    {
        $orders = Order::where('status', 'rejected')->with('customer')->get();
        return view('orders.rejected', compact('orders'));
    }
    public function completed()
    {
        $orders = Order::where('status', 'completed')->with('customer')->get();
        return view('orders.completed', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status'           => 'required|in:pending,shipping,rejected,completed',
            'delivery_service' => 'nullable|string|max:255',
        ]);

        $current = $order->status;
        $new     = $request->status;

        // Allowed transitions
        $allowedTransitions = [
            'pending'   => ['shipping', 'rejected'],
            'shipping'  => ['completed', 'rejected'],
            'completed' => [], // cannot change
            'rejected'  => [], // cannot change
        ];

        if (! in_array($new, $allowedTransitions[$current])) {
            return back()->with('error', "Cannot change order from {$current} to {$new}.");
        }

        if ($new === 'shipping' && ! $request->delivery_service) {
            return back()->with('error', 'Delivery service is required for shipping.');
        }

        $order->status           = $new;
        $order->delivery_service = $request->delivery_service;
        $order->save();

        return back()->with('success', "Order status updated to {$new}!");
    }
}
