@extends('layouts.app')

@section('content')
<h1><i class="fas fa-user"></i> Select Customer</h1>
<p>Please select a customer to create a new order:</p>

<table class="table table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Customer Name</th>
            <th>Phone</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($customers as $customer)
        <tr>
            <td>{{ $customer->id }}</td>
            <td>{{ $customer->full_name }}</td>
            <td>{{ $customer->phone_number }}</td>
            <td>
                <a href="{{ route('orders.create', $customer->id) }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Create Order
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
