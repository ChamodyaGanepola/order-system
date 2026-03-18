<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Step 1: show all customers to select
    public function selectCustomer()
    {
        $customers = Customer::all();
        return view('orders.select_customer', compact('customers'));
    }

// Step 2: show create order form (existing)
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

        $total = 0;
$outOfStockProducts = [];

foreach ($request->products as $productId => $quantity) {

    $product = Product::find($productId);

    if (!$product || $quantity <= 0) {
        continue;
    }

    if ($product->stock < $quantity) {
        $outOfStockProducts[] = "{$product->name} (Needed: $quantity, Available: $product->stock)";
        continue;
    }

    $subtotal = $product->price * $quantity;

    OrderItem::create([
        'order_id'   => $order->id,
        'product_id' => $product->id,
        'quantity'   => $quantity,
        'price'      => $product->price,
        'subtotal'   => $subtotal,
    ]);

    // ✅ REDUCE STOCK
    $product->stock -= $quantity;
    $product->save();

    // ✅ ADD TOTAL
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
        $products = Product::all();                // all products for dropdown
        $order->load('customer', 'items.product'); // eager load
        return view('orders.edit', compact('order', 'products'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'products' => 'required|array',
        ]);

        $existingItems = $order->items()->get()->keyBy('product_id'); // existing items
        $total         = 0;

        $order->items()->delete(); // remove old items

        foreach ($request->products as $productId => $quantity) {
            $product = Product::find($productId);
            if (! $product) {
                continue;
            }

            // For already existing order items, allow editing even if stock=0
            $oldQuantity = $existingItems->has($productId) ? $existingItems[$productId]->quantity : 0;

            // Only check stock if new product or increasing quantity
            if (! $existingItems->has($productId) && $quantity > $product->stock) {
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
            if (! $existingItems->has($productId)) {
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
                if (! $product) {
                    continue;
                }

                // Treat 'other' as string, default to 'N/A'
                $variant = $product->other ?: 'N/A';

                $key = $product->product_code . ' - ' . $variant;

                if (! isset($outOfStockSummary[$key])) {
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
        $order           = Order::findOrFail($orderId);
        $status          = $request->input('status');
        $deliveryService = $request->input('delivery_service');
        $city            = $request->input('city');

        // Only update to shipping if waybill will be created
        if ($status === 'shipping' && ! $order->waybill_number) {
            try {
                $weight = $request->input('weight', 1);
                if ($deliveryService === 'transexpress') {
                    // Transex API
                    $apiData = \App\Helpers\TransexHelper::createOrder($order, $deliveryService, $city);

                    if (isset($apiData['orders']['waybill_id'])) {
                        $order->waybill_number   = $apiData['orders']['waybill_id'];
                        $order->delivery_service = $deliveryService;
                    } else {
                        return response()->json(['error' => 'Transexpress did not return a waybill ID'], 400);
                    }
                } elseif ($deliveryService === 'domestic') {
                    $city   = $request->input('city'); // <- get city from request
                    $weight = $request->input('weight', 1);

                    $apiData = \App\Helpers\FDEDomesticHelper::createOrder($order, $weight, 0, $city);

                    if (isset($apiData['waybill_no'])) {
                        $order->waybill_number   = $apiData['waybill_no'];
                        $order->delivery_service = $deliveryService;
                    } else {
                        return response()->json(['error' => 'FDE Domestic did not return a waybill number'], 400);
                    }
                } else {
                    return response()->json(['error' => 'Unknown delivery service'], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['error' => 'Could not sync with carrier: ' . $e->getMessage()], 500);
            }
        }

        // Update order status
        $order->status = $status;
        $order->save();

        return response()->json([
            'success'          => 'Order status updated successfully.',
            'order_id'         => $order->id,
            'waybill_number'   => $order->waybill_number,
            'delivery_service' => $order->delivery_service,
        ]);
    }
}
