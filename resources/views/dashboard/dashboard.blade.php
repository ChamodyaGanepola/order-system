@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <h2 class="dashboard-title"><i class="fas fa-tachometer-alt"></i> Dashboard</h2>

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
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Import Date Stats</h5>
    </div>
    <div class="card-body p-0">
        @if($importDateStats->count())
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Customers Imported</th>
                    <th>Orders Created</th>
                    <th>Revenue (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($importDateStats as $stat)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($stat->import_date)->format('Y-m-d') }}</td>
                    <td>{{ $stat->customers_count }}</td>
                    <td>{{ $stat->orders_count }}</td>
                    <td>{{ number_format($stat->revenue, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="p-3 text-center text-muted">
            <i class="fas fa-info-circle fa-2x mb-2"></i>
            <p>No import data found!</p>
        </div>
        @endif
    </div>
</div>
  <!-- Out of Stock Products -->
        <div class="dashboard-card bg-danger">
            <div class="card-content">
                <div>
                    <h6><i class="fas fa-box-open"></i> Out of Stock Products</h6>
                    <h2>{{ count($outOfStockSummary) }}</h2>
                </div>
                <i class="fas fa-box-open fa-3x card-icon"></i>
            </div>
            <div style="margin-top: 10px;">
                <button class="btn btn-light btn-sm" onclick="toggleOutOfStockTable()">View Details</button>
            </div>
        </div>

    </div>

    <!-- Out of Stock Table (Initially Hidden) -->
    <div id="out-of-stock-table" style="display:none; margin-top: 30px;">
        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-box-open"></i> Out of Stock Product Details</h5>
            </div>
            <div class="card-body p-0">
                @if(count($outOfStockSummary) > 0)
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product Code</th>

                            <th>Missing Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($outOfStockSummary as $key => $missingQty)
                        @php
                            $parts = explode(' - ', $key);
                            $code = $parts[0] ?? $key;
                            $variant = $parts[1] ?? 'N/A';
                        @endphp
                        <tr>
                            <td>{{ $code }}</td>

                            <td><span class="badge bg-danger">{{ $missingQty }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="p-3 text-center text-muted">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <p>All products are in stock!</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<script>
function toggleOutOfStockTable() {
    const table = document.getElementById('out-of-stock-table');
    table.style.display = table.style.display === 'none' ? 'block' : 'none';
}
</script>


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
