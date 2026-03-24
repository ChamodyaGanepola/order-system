@extends('layouts.app')

@section('content')
@if($customers->total() > 0)
<div class="content-toolbar" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 20px;">
   <form method="GET" class="customer-search-form" style="display:flex; gap:10px; align-items:center;">
    <!-- Existing per_page selector -->
    <select name="per_page" onchange="this.form.submit()" class="form-control">
        @foreach([5, 10, 20, 50, 100] as $size)
            <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }} per page</option>
        @endforeach
    </select>

    <!-- Import date filter -->
    <select name="import_date" onchange="this.form.submit()" class="form-control">
        <option value="">All Import Dates</option>
        @foreach($importDates as $date)
            <option value="{{ $date }}" {{ request('import_date') == $date ? 'selected' : '' }}>
                {{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}
            </option>
        @endforeach
    </select>

    <input type="text" name="search" placeholder="Search Name..." value="{{ request('search') }}" class="form-control">
    <button type="submit" class="btn btn-primary">Filter</button>
</form>
<!-- Delete by Date Button -->
@if(request('import_date'))
    <form action="{{ route('customers.imports.deleteByDate') }}" method="POST" style="display:inline;"
          onsubmit="return confirm('Are you sure you want to delete ALL imports for this date?');">
        @csrf
        @method('DELETE')
        <input type="hidden" name="import_date" value="{{ request('import_date') }}">
        <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash"></i> Delete Imports for {{ \Carbon\Carbon::parse(request('import_date'))->format('Y-m-d') }}
        </button>
    </form>
@endif
 <div style="display:flex; gap:10px;">
    <a href="/customers/create" class="btn btn-primary" style="display: flex; align-items: center; gap: 6px;">
        <i class="fas fa-user"></i> <i class="fas fa-plus"></i> Add Customer
    </a>
    <form action="{{ route('customers.destroyAll') }}" method="POST"
              onsubmit="return confirm('Are you sure you want to delete ALL customers? This cannot be undone!');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" style="display:flex; align-items:center; gap:6px;">
                <i class="fas fa-trash"></i> Delete All
            </button>
        </form>
    </div>
</div>
@else
<div class="content-box" style="text-align:center; padding:40px;">
    <i class="fas fa-users" style="font-size:40px; color:#9ca3af;"></i>
    <h3 style="margin-top:10px;">No Customers Found</h3>
    <p style="color:#6b7280;">Add a customer or import from Excel to get started.</p>

    <a href="/customers/create" class="btn btn-primary" style="margin-top:10px;">
        <i class="fas fa-user-plus"></i> Add Customer
    </a>
</div>

@endif
<div class="content-box" style="margin-bottom: 30px;">
    <h3 style="display: flex; align-items: center; gap: 8px; color: var(--primary); font-weight: 700;">
        <i class="fas fa-upload"></i> Import Customers from Excel
    </h3>
    <form action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end;">
@csrf
        <div style="flex: 1; min-width: 180px;">
            <input type="file" name="file" required style="padding: 10px; border: 2px solid #e2e8f0; border-radius: 6px; width: 100%;">
        </div>
        <button type="submit" class="btn btn-success" style="display: flex; align-items: center; gap: 6px;">
            <i class="fas fa-upload"></i> Upload
        </button>
    </form>
</div>

@if($customers->total() > 0)
    <table class="table table-striped">
        <thead>
            <tr>
                <th><i class="fas fa-user"></i> Name</th>
                <th><i class="fas fa-phone"></i> Phone</th>
                <th><i class="fas fa-map-marker-alt"></i> Address</th>
                <th>Product Code</th>
<th>Order Status</th>
                <th><i class="fas fa-calendar"></i> Created Date</th>
<th><i class="fas fa-calendar-day"></i> Day</th>
                <th><i class="fas fa-cogs"></i> Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
            <tr>
                <td>{{ $customer->full_name }}</td>
                <td>{{ $customer->phone_number }}</td>
                <td>{{ $customer->street_address }}</td>
<td>
    @if($customer->product_code)
        <div>{{ $customer->product_code }}</div>
    @endif

    @if(!empty($customer->unknown_product_code))
        <div style="color:red; font-weight:bold;">
            {{ $customer->unknown_product_code }} (need to verify)
        </div>
    @endif
</td>
<td>

   @if($customer->orders->count())
    @foreach($customer->orders as $order)
        @foreach($order->items as $item)
            <div>
                {{ $item->product->product_code ?? 'N/A' }}


                <strong>x{{ $item->quantity }}</strong> :
                <span style="font-weight:600; color:
                    @if($order->status == 'pending') orange
                    @elseif($order->status == 'shipping') blue
                    @elseif($order->status == 'completed') green
                    @elseif($order->status == 'rejected') red
                    @elseif($order->status == 'out_of_stock') gray
                    @endif
                ">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
        @endforeach
    @endforeach
@else
    -
@endif
</td>
                <td>{{ $customer->created_at->format('Y-m-d H:i') }}</td>
<td>{{ $customer->created_at->format('l') }}</td>
                <td style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                    <a href="/orders/create/{{ $customer->id }}" class="btn btn-success btn-sm" style="display: flex; align-items: center; gap: 4px;">
                        <i class="fas fa-shopping-cart fa-sm"></i> Order
                    </a>
                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 4px;">
                        <i class="fas fa-edit fa-sm"></i> Edit
                    </a>
                    <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" style="display:inline;"
                     onsubmit="return confirm('Are you sure you want to delete this customer? ');">
@csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" style="display: flex; align-items: center; gap: 4px;">
                            <i class="fas fa-trash fa-sm"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
<div class="pagination-container" style="margin-top: 30px; display: flex; flex-direction: column; align-items: center; gap: 12px;">
    <!-- Custom Pagination Links -->
    <div class="pagination-links">
       {{ $customers->links('vendor.pagination.custom') }}
    </div>

    <!-- Summary -->
    <div class="pagination-summary" style="font-size: 14px; color: #555;">
        Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} results
    </div>
</div>

<style>
/* Hide default nav wrapper completely */
.pagination-links nav {
    margin: 0 !important;
    padding: 0 !important;
}

/* Flex display for page numbers */
.pagination-links .pagination {
    display: flex;
    gap: 10px; /* horizontal spacing */
    justify-content: center;
    margin: 0;
    padding: 0;
    list-style: none;
}

/* Individual page link styling */
.pagination-links .page-item .page-link {
    min-width: 36px;
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    color: #1e293b;
    font-weight: 500;
    text-align: center;
    transition: all 0.2s;
}

.pagination-links .page-item.active .page-link {
    background-color: #2563eb;
    color: white;
    border-color: #2563eb;
}

.pagination-links .page-item.disabled .page-link {
    color: #b0b0b0;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
    .pagination-links .pagination {
        flex-wrap: wrap;
        gap: 6px;
    }
}
</style>
@endif

@endsection
