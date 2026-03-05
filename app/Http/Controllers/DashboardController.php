<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCustomers = Customer::count();
        $totalProducts  = Product::count();
        $pendingOrders  = Order::where('status', 'pending')->count();
        $shippingOrders = Order::where('status', 'shipping')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $rejectedOrders = Order::where('status', 'rejected')->count();
        $totalOrders    = Order::count();
        $totalRevenue   = Order::sum('total_amount');

        return view('dashboard.dashboard', compact(
            'totalCustomers',
            'totalProducts',
            'pendingOrders',
            'shippingOrders',
            'completedOrders',
            'rejectedOrders',
            'totalOrders',
            'totalRevenue'
        ));
    }
}
