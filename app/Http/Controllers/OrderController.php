<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
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
        'products'    => 'required|array',
    ]);

    $customer = Customer::findOrFail($request->customer_id);

    // Create a new order for this customer every time
    $order = Order::create([
        'customer_id' => $customer->id,
        'status'      => 'pending',
        'pending_at'  => now(),
        'total_amount'=> 0, // initial total, will calculate later
    ]);

    $total = 0;
    $orderedProductCodes = [];
    $unknownProductCodes = [];

    foreach ($request->products as $productId => $data) {
        $quantity = $data['quantity'] ?? 1;
        $product = Product::find($productId);

        // If product not found or invalid quantity → treat as unknown
        if (! $product || $quantity <= 0) {
            if (! in_array($productId, $unknownProductCodes)) {
                $unknownProductCodes[] = $productId;
            }
            continue;
        }

        // Add to ordered codes
        if (! in_array($product->product_code, $orderedProductCodes)) {
            $orderedProductCodes[] = $product->product_code;
        }

        // Create order item
        $order->items()->create([
            'product_id' => $product->id,
            'quantity'   => $quantity,
            'price'      => $product->price,
            'subtotal'   => $product->price * $quantity,
        ]);

        // Reduce stock if enough
        if ($product->stock >= $quantity) {
            $product->stock -= $quantity;
            $product->save();
        } else {
            $order->status = 'out_of_stock';
            $order->out_of_stock_at = now();
        }

        $total += $product->price * $quantity;
    }

    // Update order total
    $order->total_amount = $total;
    $order->save();

    // Update customer codes (optional, if needed for tracking)
    if (! empty($orderedProductCodes) || ! empty($unknownProductCodes)) {
        $customer->update([
            'product_code'         => ! empty($orderedProductCodes) ? implode(',', $orderedProductCodes) : null,
            'unknown_product_code' => ! empty($unknownProductCodes) ? implode(',', array_unique($unknownProductCodes)) : null,
        ]);
    }

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



                $key = $product->product_code;

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

    public function bulkShip(Request $request)
    {
        try {
            $orders = $request->all(); // raw array

            if (! is_array($orders) || count($orders) === 0) {
                return response()->json(['error' => 'Orders array is required'], 400);
            }

            $response = \App\Helpers\TransexHelper::createBulkOrders($orders);

            if (! is_array($response) || ! isset($response['orders'])) {
                throw new \Exception('Invalid response from Transex API');
            }

            foreach ($response['orders'] as $orderResponse) {
                $orderId = $orderResponse['order_no'] ?? null;
                if (! $orderId) {
                    continue;
                }

                $o = Order::where('id', $orderId)->first();
                if (! $o) {
                    continue;
                }

                $o->status           = 'shipping';
                $o->waybill_number   = $orderResponse['waybill_id'] ?? null;
                $o->delivery_service = $orderResponse['delivery_service'] ?? ($orders[array_search($orderId, array_column($orders, 'order_id'))]['delivery_service'] ?? null); // ✅
                $o->shipping_at      = now();
                $o->save();
            }

            return response()->json(['success' => 'Bulk shipping completed']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function bulkDetails(Request $request)
    {
        $orders = Order::with('customer')->whereIn('id', $request->order_ids)->get();

        $details = $orders->map(function ($order) {
            return [
                'order_id'          => $order->id,
                'customer_name'     => $order->customer->full_name,
                'street_address'    => $order->customer->street_address,
                'order_description' => 'Order #' . $order->id,
                'phone_number'      => $order->customer->phone_number,
                'phone_number_2'    => $order->customer->phone_number_2 ?? '',
                'total_amount'      => $order->total_amount,
                'city'              => $order->customer->city ?? 864,
                'remarks'           => $order->note ?? '',
            ];
        });

        return response()->json($details);
    }
    public function updateStatus(Request $request, $orderId)
    {
        $order           = Order::findOrFail($orderId);
        $status          = $request->input('status');
        $deliveryService = $request->input('delivery_service');

        // Update order status only
        $order->status = $status;
        switch ($status) {
            case 'pending':$order->pending_at = now();
                break;
            case 'shipping':$order->shipping_at = now();
                break;
            case 'completed':$order->completed_at = now();
                break;
            case 'rejected':$order->rejected_at = now();
                break;
            case 'out_of_stock':$order->out_of_stock_at = now();
                break;
        }

        $order->save();

        return response()->json([
            'success'          => 'Order status updated successfully.',
            'order_id'         => $order->id,
            'waybill_number'   => $order->waybill_number ?? null,
            'delivery_service' => $order->delivery_service ?? null,
        ]);
    }
    public function bulkShipFDE(Request $request)
    {
        $ordersData = $request->all(); // array of orders from frontend
        $results    = [];

        foreach ($ordersData as $orderData) {
            try {
                $order = Order::findOrFail($orderData['order_id']);

                // Call FDE Domestic helper
                $response = \App\Helpers\FDEDomesticHelper::createOrder(
                    $order,
                    $parcelWeight = 1,
                    $exchange = 0,
                    $city = $orderData['city'] ?? $order->customer->city
                );

                // Save order details
                $order->status           = 'shipping';
                $order->delivery_service = 'domestic';
                $order->waybill_number   = $response['waybill_no'] ?? null;
                $order->shipping_at      = now();
                $order->save();

                $results[] = [
                    'order_id' => $order->id,
                    'success'  => true,
                    'waybill'  => $order->waybill_number,
                ];

            } catch (\Exception $e) {
                $results[] = [
                    'order_id' => $orderData['order_id'],
                    'success'  => false,
                    'error'    => $e->getMessage(),
                ];
            }
        }

        return response()->json(['results' => $results]);
    }
}
