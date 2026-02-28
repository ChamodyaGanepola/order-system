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

    <div class="pagination-wrapper">

    {{ $customers->links('vendor.pagination.bootstrap-5') }}

    <span style="white-space: nowrap;">
        Showing {{ $customers->firstItem() }}
        to {{ $customers->lastItem() }}
        of {{ $customers->total() }} results
    </span>

</div>
<style>
@media (max-width: 900px) {
    .content-toolbar, .customer-search-form {
        flex-direction: column !important;
        align-items: stretch !important;
    }
    .content-toolbar > *, .customer-search-form > * {
        width: 100% !important;
        min-width: 0 !important;
    }
    .content-toolbar {
        gap: 12px !important;
    }
    .table thead th, .table td {
        font-size: 13px !important;
        padding: 8px !important;
    }
    .table td > * {
        font-size: 13px !important;
    }
    .content-box {
        padding: 16px !important;
    }
    .pagination-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin-top: 20px;
}

.pagination-wrapper nav {
    display: inline-flex !important;
    margin: 0 !important;
}

.pagination {
    margin: 0 !important;
}

.pagination li {
    white-space: nowrap;
}
}
</style>
@endif

@endsection
