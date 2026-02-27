<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Facades\Excel;

class Sheet1Import implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    public function importFile($file)
    {
        // Force import only the first sheet (index 0)
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

        // Check for existing customer
        $existing = Customer::where('full_name', $row['full_name'])
                            ->where('street_address', $row['street_address'])
                            ->where('phone_number', $phone)
                            ->first();

        if ($existing) {
            // Update existing customer instead of creating duplicate
            $existing->update([
                'phone_number_2' => isset($row['phone_number_2']) ? $this->formatPhone($row['phone_number_2']) : $existing->phone_number_2,
                'other'          => $row['other'] ?? $existing->other,
                'product_code'   => $row['product_code'] ?? $existing->product_code,
            ]);
            return null;
        }

        return new Customer([
            'full_name'      => $row['full_name'],
            'street_address' => $row['street_address'],
            'phone_number'   => $phone,
            'phone_number_2' => isset($row['phone_number_2']) ? $this->formatPhone($row['phone_number_2']) : null,
            'other'          => $row['other'] ?? null,
            'product_code'   => $row['product_code'] ?? null,
        ]);
    }

    private function formatPhone($number)
    {
        $number = preg_replace('/[\s\-\(\)]/', '', $number);
        if (substr($number, 0, 1) === '0') $number = '+94' . substr($number, 1);
        elseif (substr($number, 0, 1) !== '+') $number = '+94' . $number;
        return $number;
    }
}
