@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <h2 class="dashboard-title"><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
<form method="GET" style="margin-bottom:20px; display:flex; gap:10px;">
    <input type="date" name="date"
    value="{{ request('date', now()->toDateString()) }}"
    class="form-control">

    <button type="submit" class="btn btn-primary">Filter</button>
</form>
    <div class="dashboard-grid">

        <!-- Customers -->
        <div class="dashboard-card bg-primary">
            <div class="card-content">
                <div>
                    <h6><i class="fas fa-users"></i> Customers</h6>
                    <h2>{{ $totalCustomers }}</h2>
                </div>
                <i class="fas fa-users fa-3x card-icon"></i>
            </div>
        </div>

        <!-- Products -->
        <div class="dashboard-card bg-success">
            <div class="card-content">
                <div>
                    <h6><i class="fas fa-boxes"></i> Products</h6>
                    <h2>{{ $totalProducts }}</h2>
                </div>
                <i class="fas fa-boxes fa-3x card-icon"></i>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="dashboard-card bg-warning">
            <div class="card-content">
                <div>
                    <h6><i class="fas fa-hourglass-half"></i> Pending Orders</h6>
                    <h2>{{ $pendingOrders }}</h2>
                </div>
                <i class="fas fa-hourglass-half fa-3x card-icon"></i>
            </div>
        </div>

        <!-- Shipping Orders -->
        <div class="dashboard-card bg-info">
            <div class="card-content">
                <div>
                    <h6><i class="fas fa-truck"></i> Shipping Orders</h6>
                    <h2>{{ $shippingOrders }}</h2>
                </div>
                <i class="fas fa-truck fa-3x card-icon"></i>
            </div>
        </div>
 <!-- Completed Orders -->
        <div class="dashboard-card bg-success">
            <div class="card-content">
                <div>
                    <h6><i class="fas fa-check-circle"></i> Completed Orders</h6>
                    <h2>{{ $completedOrders }}</h2>
                </div>
                <i class="fas fa-check-circle fa-3x card-icon"></i>
            </div>
        </div>

        <!-- Rejected Orders -->
        <div class="dashboard-card bg-danger">
            <div class="card-content">
                <div>
                    <h6><i class="fas fa-times-circle"></i> Rejected Orders</h6>
                    <h2>{{ $rejectedOrders }}</h2>
                </div>
                <i class="fas fa-times-circle fa-3x card-icon"></i>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="dashboard-card bg-secondary">
            <div class="card-content">
                <div>
                    <h6><i class="fas fa-shopping-cart"></i> Total Orders(Without Out of Stock)</h6>
                    <h2>{{ $totalOrders }}</h2>
                </div>
                <i class="fas fa-shopping-cart fa-3x card-icon"></i>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="dashboard-card bg-dark">
            <div class="card-content">
                <div>
                    <h6><i class="fas fa-rupee-sign"></i> Total Revenue</h6>
                    <h2>Rs.{{ number_format($totalRevenue, 2) }}</h2>
                </div>
                <i class="fas fa-dollar-sign fa-3x card-icon"></i>
            </div>
        </div>
        <div class="card mt-4 shadow-sm">

   
</div>

</div>



<style>
.dashboard-container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 15px;
}

.dashboard-card.bg-danger:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
}
.dashboard-title {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 30px;
    color: #1e293b;
}

/* Grid: 2 cards per row on large screens */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 20px;
}

/* Card Styles */
.dashboard-card {
    border-radius: 12px;
    color: black;
    padding: 20px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.card-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-content h6 {
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 5px;
    text-transform: uppercase;
}

.card-content h2 {
    font-size: 32px;
    font-weight: 700;
}

.card-icon {
    opacity: 0.2;
}

/* Responsive tweaks */
@media (max-width: 600px) {
    .dashboard-title { font-size: 24px; }
    .card-content h2 { font-size: 26px; }
}
</style>
@endsection
