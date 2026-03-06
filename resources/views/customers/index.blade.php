@extends('layouts.app')

@section('content')

<div class="content-toolbar" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 20px;">
    <form method="GET" class="customer-search-form" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name..." class="form-control">

        <select name="sort" onchange="this.form.submit()" class="form-control">
            <option value="asc" {{ $sort == 'asc' ? 'selected' : '' }}>Name A → Z</option>
            <option value="desc" {{ $sort == 'desc' ? 'selected' : '' }}>Name Z → A</option>
        </select>

        <select name="per_page" onchange="this.form.submit()" class="form-control">
            @foreach([5, 10, 20, 50, 100] as $size)
                <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }} per page</option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply</button>
    </form>

    <a href="/customers/create" class="btn btn-primary" style="display: flex; align-items: center; gap: 6px;">
        <i class="fas fa-user"></i> <i class="fas fa-plus"></i> Add Customer
    </a>
</div>

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
                <th><i class="fas fa-cogs"></i> Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
            <tr>
                <td>{{ $customer->full_name }}</td>
                <td>{{ $customer->phone_number }}</td>
                <td>{{ $customer->street_address }}</td>
                <td style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                    <a href="/orders/create/{{ $customer->id }}" class="btn btn-success btn-sm" style="display: flex; align-items: center; gap: 4px;">
                        <i class="fas fa-shopping-cart fa-sm"></i> Order
                    </a>
                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 4px;">
                        <i class="fas fa-edit fa-sm"></i> Edit
                    </a>
                    <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" style="display:inline;">
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
