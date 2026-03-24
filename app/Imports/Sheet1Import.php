<?php
namespace App\Imports;

use App\Helpers\AddressHelper;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\CustomerImport;
class Sheet1Import implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    protected $rowIndex = 0;
    protected $importBatch;

    public function __construct()
    {
        $this->importBatch = (Customer::max('import_batch') ?? 0) + 1;
    }

    public function importFile($file)
    {
        \Maatwebsite\Excel\Facades\Excel::import($this, $file);
    }

    public function model(array $row)
    {
        $this->rowIndex++;

        // Normalize headers
        $row = array_change_key_case($row, CASE_LOWER);
        $row = array_combine(
            array_map(fn($key) => trim(str_replace(' ', '_', $key)), array_keys($row)),
            $row
        );

        if (empty($row['full_name']) || empty($row['street_address']) || empty($row['phone_number'])) {
            Log::warning('Missing customer info', $row);
            return null;
        }

        $phone       = $this->formatPhone($row['phone_number']);
        $addressData = AddressHelper::parseAddress($row['street_address']);

        // Find or create customer
        $customer = Customer::firstOrCreate(
            [
                'full_name'      => $row['full_name'],
                'street_address' => $addressData['street_address'],
            ],
            [
                'phone_number'   => $phone,
                'phone_number_2' => isset($row['phone_number_2']) ? $this->formatPhone($row['phone_number_2']) : null,
                'city'           => $addressData['city'],
                'district'       => $addressData['district'],
                'province'       => $addressData['province'],
                'row_order'      => $this->rowIndex,
                'import_batch'   => $this->importBatch,
            ]
        );

        // ✅ ONLY product_code (NO variant)
        $productCode = $row['product_code'] ?? null;

        if (!$productCode) {
            return $customer;
        }

        $product = Product::where('product_code', $productCode)->first();

        if (!$product) {
            // Unknown product
            $existingUnknowns = $customer->unknown_product_code
                ? explode(',', $customer->unknown_product_code)
                : [];

            if (!in_array($productCode, $existingUnknowns)) {
                $existingUnknowns[] = $productCode;
            }

            $customer->update([
                'unknown_product_code' => implode(',', $existingUnknowns),
            ]);

            return $customer;
        }

       $productCode = $row['product_code'] ?? null;

// Always create import record (even if same customer)
CustomerImport::create([
    'customer_id'  => $customer->id,
    'product_code' => $productCode,
    'imported_at'  => now(),
    'import_batch' => $this->importBatch,
]);



        return $customer;
    }

    private function formatPhone($number)
    {
        $number = preg_replace('/[\s\-\(\)]/', '', $number);

        if (substr($number, 0, 1) === '0') {
            return '+94' . substr($number, 1);
        }

        if (substr($number, 0, 1) !== '+') {
            return '+94' . $number;
        }

        return $number;
    }
}
