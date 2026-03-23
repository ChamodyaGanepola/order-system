<?php
namespace App\Http\Controllers;
use App\Helpers\AddressHelper;
use App\Models\Customer;
use Illuminate\Http\Request;

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
    public function index(Request $request)
    {
        $query = Customer::query();

        // Search
        if ($request->filled('search')) {
            $query->where('full_name', 'like', '%' . $request->search . '%');
        }

                                              // Sorting
        $sort = $request->get('sort', 'asc'); // default A-Z
        $sort = $request->get('sort', 'latest');

        if ($sort == 'latest') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sort == 'oldest') {
            $query->orderBy('created_at', 'asc');
        } elseif ($sort == 'asc') {
            $query->orderBy('full_name', 'asc');
        } elseif ($sort == 'desc') {
            $query->orderBy('full_name', 'desc');
        }

        // Pagination: default 10, allow user to select
        $perPage = $request->get('per_page', 10);
$customers = Customer::orderBy('import_batch', 'desc') // latest Excel first
                     ->orderBy('row_order', 'asc')    // rows in same Excel
                     ->with('orders')
                     ->paginate($perPage)
                     ->appends($request->all());


        return view('customers.index', compact('customers', 'sort', 'perPage'));
    }
    /*public function index()
    {
        $customers = Customer::all();
        return view('customers.index', compact('customers'));
    }
*/


public function store(Request $request)
{
    $request->validate([
        'full_name'      => 'required',
        'phone_number'   => 'required',
        'street_address' => 'required',
    ]);

    $addressData = AddressHelper::parseAddress($request->street_address);

    Customer::create([
        'full_name'      => $request->full_name,
        'phone_number'   => $request->phone_number,
        'phone_number_2' => $request->phone_number_2 ?? null,
        'street_address' => $addressData['street_address'],
        'city'           => $addressData['city'],
        'district'       => $addressData['district'],
        'province'       => $addressData['province'],
        'other'          => $request->other ?? null,
        'product_code'   => $request->product_code ?? null,
    ]);

    return redirect('/customers')->with('success', 'Customer added!');
}

public function update(Request $request, Customer $customer)
{
    $request->validate([
        'full_name'      => 'required',
        'phone_number'   => 'required',
        'street_address' => 'required',
    ]);

    $addressData = AddressHelper::parseAddress($request->street_address);

$city = $request->city ?? $addressData['city'];
$district = $request->district ?? $addressData['district'];
$province = $request->province ?? $addressData['province'];

$customer->update([
    'full_name'      => $request->full_name,
    'phone_number'   => $request->phone_number,
    'phone_number_2' => $request->phone_number_2 ?? null,
    'street_address' => $addressData['street_address'],
    'city'           => $city,
    'district'       => $district,
    'province'       => $province,
    'other'          => $request->other ?? null,
    'product_code'   => $request->product_code ?? null,
]);

    return redirect('/customers')->with('success', 'Customer updated successfully!');
}
    public function create()
    {
        return view('customers.create');
    }


    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }


    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect('/customers')->with('success', 'Customer deleted successfully!');
    }
    public function destroyAll()
    {
        Customer::query()->delete(); // ✅ works safely

        return redirect('/customers')->with('success', 'All customers deleted successfully!');
    }

}
