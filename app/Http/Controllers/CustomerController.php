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
        $query = Customer::query()->with('orders');

        // Search by name
        if ($request->filled('search')) {
            $query->where('full_name', 'like', '%' . $request->search . '%');
        }

        // Filter by import date
        if ($request->filled('import_date')) {
            $date = $request->import_date;
            $query->whereHas('imports', function ($q) use ($date) {
                $q->whereDate('imported_at', $date);
            });
        }

        // Sorting (default is by Excel upload order)
        $sort = $request->get('sort', 'latest');
        if ($sort == 'latest') {
            $query->orderBy('import_batch', 'desc') // latest Excel first
                ->orderBy('row_order', 'asc');          // rows in same Excel
        } elseif ($sort == 'oldest') {
            $query->orderBy('import_batch', 'asc')
                ->orderBy('row_order', 'asc');
        } elseif ($sort == 'asc') {
            $query->orderBy('full_name', 'asc');
        } elseif ($sort == 'desc') {
            $query->orderBy('full_name', 'desc');
        }

        // Pagination
        $perPage   = $request->get('per_page', 10);
        $customers = $query->paginate($perPage)->appends($request->all());

        // Get all unique import dates for the filter dropdown
        $importDates = \App\Models\CustomerImport::selectRaw('DATE(imported_at) as import_date')
            ->distinct()
            ->orderBy('import_date', 'desc')
            ->pluck('import_date');

        return view('customers.index', compact('customers', 'sort', 'perPage', 'importDates'));
    }
    public function deleteImportsByDate(Request $request)
    {
        $request->validate([
            'import_date' => 'required|date',
        ]);

        $date = $request->import_date;

        // Delete all CustomerImport records for this date
        \App\Models\CustomerImport::whereDate('imported_at', $date)->delete();

        return redirect()->back()->with('success', "All imports for $date deleted successfully!");
    }

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
            'product_code'   => $request->product_code ?? null,
        ]);

        return redirect('/customers')->with('success', 'Customer added!');
    }
    public function deleteUnknown(Customer $customer)
    {
        $customer->unknown_product_code = null;
        $customer->save();

        return redirect()->back()->with('success', 'Unknown product code deleted.');
    }
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'full_name'      => 'required',
            'phone_number'   => 'required',
            'street_address' => 'required',
        ]);

        $addressData = AddressHelper::parseAddress($request->street_address);

        $city     = $request->city ?? $addressData['city'];
        $district = $request->district ?? $addressData['district'];
        $province = $request->province ?? $addressData['province'];

        $customerData = [
            'full_name'            => $request->full_name,
            'phone_number'         => $request->phone_number,
            'phone_number_2'       => $request->phone_number_2 ?? null,
            'street_address'       => $addressData['street_address'],
            'city'                 => $city,
            'district'             => $district,
            'province'             => $province,
            'unknown_product_code' => $request->unknown_product_code,
        ];

// Only update product_code if provided
        if ($request->filled('product_code')) {
            $customerData['product_code'] = $request->product_code;
        }

        $customer->update($customerData);

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
