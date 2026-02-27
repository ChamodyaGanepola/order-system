<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
   public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv',
    ]);

    $import = new \App\Imports\Sheet1Import();
    $import->importFile($request->file('file')); // Only imports first sheet

    return redirect('/customers')->with('success', 'Customers imported!');
}

    public function index()
    {
        $customers = Customer::all();
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name'      => 'required',
            'phone_number'   => 'required',
            'street_address' => 'required',
        ]);

        Customer::create($request->all());

        return redirect('/customers')->with('success', 'Customer added!');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'full_name'      => 'required',
            'phone_number'   => 'required',
            'street_address' => 'required',
            'phone_number_2' => 'nullable',
            'other'          => 'nullable',
            'product_code'   => 'nullable',
        ]);

        $customer->update($request->all());

        return redirect('/customers')->with('success', 'Customer updated successfully!');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect('/customers')->with('success', 'Customer deleted successfully!');
    }

}
