@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1>Customers Management</h1>
    <a href="/customers/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Customer
    </a>
</div>

<div class="content-box" style="margin-bottom: 30px;">
    <h3><i class="fas fa-upload"></i> Import Customers from Excel</h3>
    <form action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: flex-end;">
        @csrf
        <div style="flex: 1;">
            <input type="file" name="file" required style="padding: 10px; border: 2px solid #e2e8f0; border-radius: 6px; width: 100%;">
        </div>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-upload"></i> Upload
        </button>
    </form>
</div>

@if($customers->count() > 0)
<table>
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
            <td><strong>{{ $customer->full_name }}</strong></td>
            <td>{{ $customer->phone_number }}</td>
            <td>{{ $customer->street_address }}</td>
            <td>
                <div class="action-buttons">
                    <a href="/orders/create/{{ $customer->id }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Order
                    </a>
                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="empty-state">
    <i class="fas fa-users"></i>
    <h3>No Customers Found</h3>
    <p>Start by adding your first customer or importing from Excel</p>
    <a href="/customers/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Customer
    </a>
</div>
@endif
@endsection
