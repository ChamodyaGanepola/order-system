@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1><i class="fas fa-user-plus"></i> Add New Customer</h1>
</div>

<div class="content-box" style="max-width: 600px;">
    <form action="/customers" method="POST">
    @csrf

        <div class="form-group">
            <label for="full_name"><i class="fas fa-user"></i> Full Name *</label>
            <input type="text" id="full_name" name="full_name" required placeholder="Customer full name">
        </div>

        <div class="form-group">
            <label for="phone_number"><i class="fas fa-phone"></i> Phone Number *</label>
            <input type="text" id="phone_number" name="phone_number" required placeholder="e.g. +94..." maxlength="20">
        </div>

        <div class="form-group">
            <label for="phone_number_2"><i class="fas fa-phone"></i> Phone Number 2</label>
            <input type="text" id="phone_number_2" name="phone_number_2" placeholder="Secondary phone number" maxlength="20">
        </div>

        <div class="form-group">
            <label for="street_address"><i class="fas fa-map-marker-alt"></i> Address *</label>
            <textarea id="street_address" name="street_address" required placeholder="Full street address"></textarea>
        </div>

        <div class="form-group">
            <label for="other"><i class="fas fa-info-circle"></i> Additional Notes</label>
            <input type="text" id="other" name="other" placeholder="Any additional information">
        </div>

        <div class="form-group">
            <label for="product_code"><i class="fas fa-barcode"></i> Product Code</label>
            <input type="text" id="product_code" name="product_code" placeholder="Product code or SKU">
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Add Customer
            </button>
            <a href="/customers" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Cancel
            </a>
        </div>
    </form>
</div>
@endsection
