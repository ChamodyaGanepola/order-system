<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Basic stats
        $totalCustomers   = Customer::count();
        $totalProducts    = Product::count();
        $pendingOrders    = Order::where('status', 'pending')->count();
        $shippingOrders   = Order::where('status', 'shipping')->count();
        $completedOrders  = Order::where('status', 'completed')->count();
        $rejectedOrders   = Order::where('status', 'rejected')->count();
        $outOfStockOrders = Order::where('status', 'out_of_stock')->count();
        $totalOrders = Order::where('status', '!=', 'out_of_stock')->count();
        $totalRevenue     = Order::sum('total_amount');

        // Out-of-stock summary
        $outOfStockSummary = [];

        // Get all out-of-stock orders
        $outOfStockOrdersList = Order::where('status', 'out_of_stock')
            ->with('items.product')
            ->get();

        foreach ($outOfStockOrdersList as $order) {
            foreach ($order->items as $item) {
                $product = $item->product;
                if (!$product) continue;

                $variant = $product->other ?: 'N/A';
                $key = $product->product_code . ' - ' . $variant;

                if (!isset($outOfStockSummary[$key])) {
                    $outOfStockSummary[$key] = 0;
                }

                // accumulate total missing count
                $outOfStockSummary[$key] += $item->quantity;
            }
        }

        return view('dashboard.dashboard', compact(
            'totalCustomers',
            'totalProducts',
            'pendingOrders',
            'shippingOrders',
            'completedOrders',
            'rejectedOrders',
            'outOfStockOrders',
            'totalOrders',
            'totalRevenue',
            'outOfStockSummary'
        ));
    }
}
