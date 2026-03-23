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
        \Maatwebsite\Excel\Facades\Excel::import($this, $file, null, \Maatwebsite\Excel\Excel::XLSX, [
            'sheets' => [0],
        ]);
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

    // Check for existing customer
    $customer = Customer::where('full_name', $row['full_name'])
        ->where('street_address', $addressData['street_address'])
        ->first();

    if (!$customer) {
        // Create new customer
        $customer = Customer::create([
            'full_name'      => $row['full_name'],
            'phone_number'   => $phone,
            'phone_number_2' => isset($row['phone_number_2']) ? $this->formatPhone($row['phone_number_2']) : null,
            'street_address' => $addressData['street_address'],
            'city'           => $addressData['city'],
            'district'       => $addressData['district'],
            'province'       => $addressData['province'],
            'product_code'   => null,
            'other'          => $row['other'] ?? null,
            'row_order'      => $this->rowIndex,
            'import_batch'   => $this->importBatch,
        ]);
    }

    // --- Handle product codes ---
    $variant     = isset($row['other']) ? strtolower(trim($row['other'])) : 'n/a';
    $productCode = $row['product_code'] ?? null;

    if ($productCode) {
        $product = Product::where('product_code', $productCode)
            ->whereRaw('LOWER(other) = ?', [$variant])
            ->first();

        if (!$product) {
            // Mark as unknown, keep any existing valid code
            $customer->update([
                'unknown_product_code' => $productCode,
            ]);
            return $customer;
        } else {
            // Set valid product code
            $customer->update([
                'product_code' => $productCode,
            ]);

            // Clear unknown if it matches this code
            if ($customer->unknown_product_code === $productCode) {
                $customer->update([
                    'unknown_product_code' => null,
                ]);
            }
        }

        // --- Order Handling ---
        $quantity = $row['quantity'] ?? 1;
        $status   = $product->stock < $quantity ? 'out_of_stock' : 'pending';

        // Find or create pending order for customer
        $order = Order::where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            $order = Order::create([
                'customer_id'  => $customer->id,
                'total_amount' => 0,
                'status'       => $status,
            ]);
        }

        // Add or update order item
        $orderItem = $order->items()->where('product_id', $product->id)->first();
        if ($orderItem) {
            $orderItem->quantity += $quantity;
            $orderItem->subtotal += $product->price * $quantity;
            $orderItem->save();
        } else {
            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $product->id,
                'quantity'   => $quantity,
                'price'      => $product->price,
                'subtotal'   => $product->price * $quantity,
            ]);
        }

        // Reduce stock if pending
        if ($status === 'pending') {
            $product->stock -= $quantity;
            $product->save();
        }

        // Update order total
        $order->total_amount = $order->items()->sum('subtotal');
        $order->save();
    }

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
