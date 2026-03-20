<?php
namespace App\Imports;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
   use App\Helpers\AddressHelper;
class Sheet1Import implements ToModel, WithHeadingRow, WithCalculatedFormulas
{

    public function importFile($file)
    {
        Excel::import($this, $file, null, \Maatwebsite\Excel\Excel::XLSX, [
            'sheets' => [0],
        ]);
    }

    public function model(array $row)
    {
        // Log the raw row so we can see the keys
        Log::info('Raw row keys: ', array_keys($row));

        // Normalize headers: lowercase + replace spaces with underscores
        $row = array_change_key_case($row, CASE_LOWER);
        $row = array_combine(
            array_map(fn($key) => trim(str_replace(' ', '_', $key)), array_keys($row)),
            $row
        );

        Log::info('Normalized row keys: ', array_keys($row));

        // Check required customer fields
        if (empty($row['full_name']) || empty($row['street_address']) || empty($row['phone_number'])) {
            Log::warning('Missing customer info: ', $row);
            return null;
        }

        // Format phone
        $phone = $this->formatPhone($row['phone_number']);

        // Create or get customer


$addressData = AddressHelper::parseAddress($row['street_address']);

$customer = Customer::firstOrCreate(
    [
        'full_name'      => $row['full_name'],
        'street_address' => $row['street_address'],
        'phone_number'   => $phone,
    ],
    [
        'phone_number_2' => isset($row['phone_number_2']) ? $this->formatPhone($row['phone_number_2']) : null,
        'city'           => $addressData['city'],
        'district'       => $addressData['district'],
        'province'       => $addressData['province'],
    ]
);

        // Get variant
        $variant = isset($row['other']) ? trim(strtolower($row['other'])) : 'n/a';

        // Get product code safely
        $productCode = $row['product_code'] ?? null;

        if (! $productCode) {
            Log::warning('Missing product code in row', $row);
            return $customer; // skip order creation but still create customer
        }

        // Find product by code + variant
        $product = Product::where('product_code', $productCode)
            ->whereRaw('LOWER(other) = ?', [$variant])
            ->first();

        if (! $product) {
            Log::warning("Product not found: code={$productCode}, variant={$variant}");
            return $customer;
        }

        // Quantity
        $quantity = isset($row['quantity']) && $row['quantity'] > 0 ? $row['quantity'] : 1;

        // Status
        $status = $product->stock < $quantity ? 'out_of_stock' : 'pending';

        // Create order

        $order = Order::where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->first();

        if ($order) {
            // Add to existing order
            $order->total_amount += $product->price * $quantity;
            $order->save();
        } else {
            // Create a new order
            $order = Order::create([
                'customer_id'  => $customer->id,
                'total_amount' => $product->price * $quantity,
                'status'       => $status,
            ]);
        }

        // Create order item
        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => $quantity,
            'price'      => $product->price,
            'subtotal'   => $product->price * $quantity,
        ]);

        // Reduce stock if pending
        if ($status === 'pending') {
            $product->stock -= $quantity;
            $product->save();
        }

        Log::info("Order created: customer={$customer->id}, product={$productCode}, quantity={$quantity}, status={$status}");

        return $customer;
    }

    private function formatPhone($number)
    {
        $number = preg_replace('/[\s\-\(\)]/', '', $number);
        if (substr($number, 0, 1) === '0') {
            $number = '+94' . substr($number, 1);
        } elseif (substr($number, 0, 1) !== '+') {
            $number = '+94' . $number;
        }

        return $number;
    }
}
