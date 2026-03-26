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
    public function index(Request $request)
{
    // ✅ Selected date (default today)
    $date = $request->input('date', now()->toDateString());

    // ✅ Customers (by import date)
    $totalCustomers = Customer::whereHas('imports', function ($q) use ($date) {
        $q->whereDate('imported_at', $date);
    })->count();

    // ✅ Products (no filter)
    $totalProducts = Product::count();

    // ✅ Orders by status (each uses correct column)
    $pendingOrders = Order::where('status', 'pending')
        ->whereDate('pending_at', $date)
        ->count();

    $shippingOrders = Order::where('status', 'shipping')
        ->whereDate('shipping_at', $date)
        ->count();

    $completedOrders = Order::where('status', 'completed')
        ->whereDate('completed_at', $date)
        ->count();

    $rejectedOrders = Order::where('status', 'rejected')
        ->whereDate('rejected_at', $date)
        ->count();

    $outOfStockOrders = Order::where('status', 'out_of_stock')
        ->whereDate('out_of_stock_at', $date)
        ->count();

    // ✅ TOTAL ORDERS (clean logic)
    $totalOrders = $pendingOrders + $shippingOrders + $completedOrders + $rejectedOrders;

    // ✅ REVENUE (only valid earning statuses)
    $totalRevenue = Order::where(function ($q) use ($date) {
        $q->where(function ($q2) use ($date) {
            $q2->where('status', 'pending')->whereDate('pending_at', $date);
        })->orWhere(function ($q2) use ($date) {
            $q2->where('status', 'shipping')->whereDate('shipping_at', $date);
        })->orWhere(function ($q2) use ($date) {
            $q2->where('status', 'completed')->whereDate('completed_at', $date);
        });
    })->sum('total_amount');

    // ✅ Import stats (latest first)
    $importDateStats = CustomerImport::select(
            DB::raw('DATE(imported_at) as import_date'),
            DB::raw('COUNT(DISTINCT customer_id) as customers_count'),
        )
        ->groupBy('import_date')
        ->orderBy('import_date', 'desc') // ✅ latest first
        ->get();

    return view('dashboard.dashboard', compact(
        'date',
        'totalCustomers',
        'totalProducts',
        'pendingOrders',
        'shippingOrders',
        'completedOrders',
        'rejectedOrders',
        'outOfStockOrders',
        'totalOrders',
        'totalRevenue',
        'importDateStats'
    ));
}
}
