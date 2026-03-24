<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
    use App\Models\CustomerImport;
use Illuminate\Support\Facades\DB;
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
    $totalOrders      = Order::where('status', '!=', 'out_of_stock')->count();
    $totalRevenue     = Order::sum('total_amount');

    // Out-of-stock summary
    $outOfStockSummary = [];
    $outOfStockOrdersList = Order::where('status', 'out_of_stock')
        ->with('items.product')
        ->get();
    foreach ($outOfStockOrdersList as $order) {
        foreach ($order->items as $item) {
            if (!$item->product) continue;
            $key = $item->product->product_code;
            $outOfStockSummary[$key] = ($outOfStockSummary[$key] ?? 0) + $item->quantity;
        }
    }



$importDateStats = CustomerImport::select(
        DB::raw('DATE(imported_at) as import_date'),
        DB::raw('COUNT(DISTINCT customer_id) as customers_count'),
    )
    ->groupBy('import_date')
    ->orderBy('import_date', 'desc')
    ->get()
    ->map(function ($import) {
        // Get total orders for customers imported that day
        $ordersCount = \App\Models\Order::whereIn('customer_id', function($query) use ($import) {
            $query->select('customer_id')
                  ->from('customer_imports')
                  ->whereDate('imported_at', $import->import_date);
        })->count();

        $revenue = \App\Models\Order::whereIn('customer_id', function($query) use ($import) {
            $query->select('customer_id')
                  ->from('customer_imports')
                  ->whereDate('imported_at', $import->import_date);
        })->sum('total_amount');

        $import->orders_count = $ordersCount;
        $import->revenue = $revenue;

        return $import;
    });

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
        'outOfStockSummary',
        'importDateStats' // pass to blade
    ));
}
}
