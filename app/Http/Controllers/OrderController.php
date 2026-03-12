<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\TransexHelper;
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
            'products'    => 'required|array', // array of ['product_code' => ..., 'other' => [...], 'quantity' => ...]
        ]);

        $order = Order::create([
            'customer_id'  => $request->customer_id,
            'total_amount' => 0,
            'status'       => 'pending',
        ]);

        $total              = 0;
        $outOfStockProducts = [];

       foreach ($request->products as $itemData) {
    $quantity    = isset($itemData['quantity']) ? (int) $itemData['quantity'] : 1;
    $productCode = $itemData['product_code'] ?? null;
    $variant     = $itemData['other'] ?? 'N/A'; // exact variant

    if (!$productCode || $quantity <= 0) continue;

    // Find the product with that variant
    $product = Product::where('product_code', $productCode)
                      ->where('other', $variant)
                      ->first();

    if (!$product) continue;

    if ($product->stock < $quantity) {
        $outOfStockProducts[] = $product->name . " ($variant) (Needed: $quantity, Available: $product->stock)";
        continue;
    }

    $subtotal = $product->price * $quantity;

    OrderItem::create([
        'order_id'   => $order->id,
        'product_id' => $product->id, // links to the variant
        'quantity'   => $quantity,
        'price'      => $product->price,
        'subtotal'   => $subtotal,
    ]);

    $product->stock -= $quantity;
    $product->save();

    $total += $subtotal;
}

        $order->total_amount = $total;

        // If any stock missing
        if (count($outOfStockProducts) > 0) {
            $order->status = 'out_of_stock';
            $order->save();

            $productList = implode(", ", $outOfStockProducts);

            return redirect('/orders/outofstock')
                ->with('error', "Stock not enough for: $productList");
        }

        $order->save();

        return redirect('/orders/pending')->with('success', 'Order created successfully!');
    }
    public function edit(Order $order)
{
    $products = Product::all(); // all products for dropdown
    $order->load('customer', 'items.product'); // eager load
    return view('orders.edit', compact('order', 'products'));
}

public function update(Request $request, Order $order)
{
    $request->validate([
        'products' => 'required|array',
    ]);

    $existingItems = $order->items()->get()->keyBy('product_id'); // existing items
    $total = 0;

    $order->items()->delete(); // remove old items

    foreach ($request->products as $productId => $quantity) {
        $product = Product::find($productId);
        if (!$product) continue;

        // For already existing order items, allow editing even if stock=0
        $oldQuantity = $existingItems->has($productId) ? $existingItems[$productId]->quantity : 0;

        // Only check stock if new product or increasing quantity
        if (!$existingItems->has($productId) && $quantity > $product->stock) {
            return back()->with('error', "Cannot add {$product->name}. Stock not enough.");
        }

        $subtotal = $product->price * $quantity;

        $order->items()->create([
            'product_id' => $product->id,
            'quantity'   => $quantity,
            'price'      => $product->price,
            'subtotal'   => $subtotal,
        ]);

        // Reduce stock only for newly added quantity
        if (!$existingItems->has($productId)) {
            $product->stock -= $quantity;
            $product->save();
        }

        $total += $subtotal;
    }

    $order->total_amount = $total;
    $order->save();

    return redirect()->route('orders.index')->with('success', 'Order updated successfully!');
}
    // In your OrderController@outOfStock
    public function outOfStock()
{
    $orders = Order::where('status', 'out_of_stock')
        ->with('customer', 'items.product')
        ->paginate(10);

    $outOfStockSummary = [];

    foreach ($orders as $order) {
        foreach ($order->items as $item) {
            $product = $item->product;
            if (!$product) continue;

            // Treat 'other' as string, default to 'N/A'
            $variant = $product->other ?: 'N/A';

            $key = $product->product_code . ' - ' . $variant;

            if (!isset($outOfStockSummary[$key])) {
                $outOfStockSummary[$key] = 0;
            }

            $outOfStockSummary[$key] += $item->quantity;
        }
    }

    return view('orders.outofstock', compact('orders', 'outOfStockSummary'));
}

    // Optional: show all pending orders
    public function pending(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $orders  = Order::where('status', 'pending')
            ->with('customer')
            ->paginate($perPage);

        return view('orders.pending', compact('orders', 'perPage'));
    }
    // Show all orders
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10); // use the value from request or default 10
        $orders  = Order::with('customer')
            ->paginate($perPage);

        return view('orders.index', compact('orders', 'perPage'));
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

public function updateStatus(Request $request, $orderId)
{
    $order = Order::findOrFail($orderId);
    $status = $request->input('status');
    $deliveryService = $request->input('delivery_service');
    $city = $request->input('city');

    if ($status === 'shipping' && !$order->waybill_number) {
        try {
            $apiData = TransexHelper::createOrder($order, $city);

            // Response: { "success": "...", "orders": { "waybill_id": "...", ... } }
            $waybillId = $apiData['orders']['waybill_id'] ?? null;

            if ($waybillId) {
                $order->waybill_number = $waybillId;
                $order->delivery_service = $deliveryService;

                Log::info('Transex waybill created', [
                    'order_id'   => $order->id,
                    'waybill_id' => $waybillId,
                    'service'    => $deliveryService,
                ]);
            } else {
                Log::error('Transex API returned unexpected structure', [
                    'order_id' => $order->id,
                    'response' => $apiData,
                ]);

                return back()->with(
                    'error',
                    'Transexpress returned an unexpected response. Check logs for details.'
                );
            }
        } catch (\Exception $e) {
            Log::error('Shipping API failed', [
                'order_id' => $order->id,
                'message'  => $e->getMessage(),
            ]);

            return back()->with(
                'error',
                'Could not sync with Transexpress: ' . $e->getMessage()
            );
        }
    }

    $order->status = $status;
    $order->save();

    return back()->with('success', 'Order status updated.');
}
}
