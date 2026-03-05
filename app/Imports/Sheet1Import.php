<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Facades\Excel;

class Sheet1Import implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    public function importFile($file)
    {
        Excel::import($this, $file, null, \Maatwebsite\Excel\Excel::XLSX, [
            'sheets' => [0]
        ]);
    }

    public function model(array $row)
{
    if (empty($row['full_name']) || empty($row['street_address']) || empty($row['phone_number'])) {
        return null;
    }

    $phone = $this->formatPhone($row['phone_number']);

    $customer = Customer::firstOrCreate(
        [
            'full_name' => $row['full_name'],
            'street_address' => $row['street_address'],
            'phone_number' => $phone,
        ],
        [
            'phone_number_2' => isset($row['phone_number_2']) ? $this->formatPhone($row['phone_number_2']) : null,
            'other' => $row['other'] ?? null,
            'product_code' => $row['product_code'] ?? null,
        ]
    );

    if (!empty($row['product_code'])) {

        $product = Product::where('product_code', $row['product_code'])
            ->whereJsonContains('other', $row['other'])
            ->first();

        if ($product) {

            $quantity = isset($row['quantity']) && $row['quantity'] > 0 ? $row['quantity'] : 1;

            $order = Order::create([
                'customer_id'  => $customer->id,
                'total_amount' => $product->price * $quantity,
                'status'       => 'pending',
            ]);

            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $product->id,
                'quantity'   => $quantity,
                'price'      => $product->price,
                'subtotal'   => $product->price * $quantity,
            ]);

            $product->stock -= $quantity;
            $product->save();
        }
    }

    return $customer;
}

    private function formatPhone($number)
    {
        $number = preg_replace('/[\s\-\(\)]/', '', $number);
        if (substr($number, 0, 1) === '0') $number = '+94' . substr($number, 1);
        elseif (substr($number, 0, 1) !== '+') $number = '+94' . $number;
        return $number;
    }
}
